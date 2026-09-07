<?php

declare(strict_types=1);

namespace Sys\Helper;

class Svg
{
    private string $iconsPath = DOCROOT . 'assets/icons/';
    private array $filesCache = [];
    private array $defaultAttributes = [];

    public function __construct(array $attributes = [])
    {
        $defaultAttributes = config('svg_defaults') ?? [];
        $this->defaultAttributes = array_replace($defaultAttributes, $attributes);
    }

    public function render(string $name, array $attributes = []): string
    {
        if (!str_ends_with($name, '.svg')) {
            $name .= '.svg';
        }

        // 1. Определяем флаг полной очистки с приоритетом
        if (isset($attributes['clean'])) {
            $shouldClean = filter_var($attributes['clean'], FILTER_VALIDATE_BOOLEAN);
        } elseif (isset($this->defaultAttributes['clean'])) {
            $shouldClean = filter_var($this->defaultAttributes['clean'], FILTER_VALIDATE_BOOLEAN);
        } else {
            $shouldClean = false;
        }

        // Удаляем флаг clean из исходного массива, чтобы он не попал в HTML
        unset($attributes['clean']);

        // 2. Читаем и обрабатываем файл только 1 раз за запрос
        $cacheKey = $name . ($shouldClean ? '_cleaned' : '_raw');

        if (!isset($this->filesCache[$cacheKey])) {
            $filePath = $this->iconsPath . $name;

            if (!file_exists($filePath)) {
                return "<!-- Icon '$name' not found -->";
            }

            $svgContent = file_get_contents($filePath);

            if ($shouldClean) {
                $svgContent = $this->cleanSvg($svgContent);
            }

            $this->filesCache[$cacheKey] = $svgContent;
        }

        $svgContent = $this->filesCache[$cacheKey];

        // 3. Логика объединения атрибутов:
        // ЕСЛИ включена полная очистка, дефолтные настройки проекта ИГНОРИРУЮТСЯ.
        if ($shouldClean) {
            $mergedRawAttributes = $attributes;
        } else {
            // Иначе объединяем, где локальные атрибуты из Twig имеют приоритет
            $defaultAttributesWithoutClean = $this->defaultAttributes;
            unset($defaultAttributesWithoutClean['clean']);
            $mergedRawAttributes = array_merge($defaultAttributesWithoutClean, $attributes);
        }

        // 4. Нормализуем ключи (переводим camelCase в kebab-case)
        $normalizedAttributes = [];
        foreach ($mergedRawAttributes as $key => $value) {
            $kebabKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $key));
            $normalizedAttributes[$kebabKey] = $value;
        }

        // 5. Логика доступности (Accessibility)
        $altText = $normalizedAttributes['alt'] ?? null;
        unset($normalizedAttributes['alt']);

        if ($altText) {
            $titleId = 'icon-title-' . uniqid('', true);
            $normalizedAttributes['aria-labelledby'] = $titleId;
            $normalizedAttributes['role'] = 'img';
            unset($normalizedAttributes['aria-hidden']);

            $titleTag = sprintf('<title id="%s">%s</title>', $titleId, htmlspecialchars($altText, ENT_QUOTES, 'UTF-8'));
            $svgContent = preg_replace('/(<svg[^>]*>)/i', '$1' . $titleTag, $svgContent, 1);
        } else {
            if (!isset($normalizedAttributes['aria-hidden'])) {
                $normalizedAttributes['aria-hidden'] = 'true';
            }
        }

        // 6. Умное удаление дубликатов атрибутов
        if (!empty($normalizedAttributes)) {
            $svgContent = preg_replace_callback('/<svg([^>]*)>/i', function ($matches) use ($normalizedAttributes) {
                // $matches[0] — это строка самого тега, например '<svg width="24" height="24">'
                $svgTagBody = $matches[0];

                foreach (array_keys($normalizedAttributes) as $attrName) {
                    $svgTagBody = preg_replace(
                        '/\b' . preg_quote($attrName, '/') . '\s*=\s*"[^"]*"/i',
                        '',
                        $svgTagBody
                    );
                }

                return $svgTagBody;
            }, $svgContent, 1);
        }
        // 7. Формируем строку HTML-атрибутов
        $htmlAttributes = [];
        foreach ($normalizedAttributes as $key => $value) {
            $htmlAttributes[] = sprintf('%s="%s"', htmlspecialchars($key, ENT_QUOTES, 'UTF-8'), htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
        }
        $attributesString = implode(' ', $htmlAttributes);

        return preg_replace('/<svg([^>]*)>/i', '<svg$1 ' . $attributesString . '>', $svgContent, 1);
    }

    /**
     * Очищает SVG от инлайновых размеров и жестко заданных цветов.
     */
    private function cleanSvg(string $svgContent): string
    {
        if (empty($svgContent)) {
            return '';
        }

        // Подавляем ошибки парсинга некорректного XML/HTML
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument();
        // Загружаем SVG как UTF-8 строку
        $dom->loadXML($svgContent, LIBXML_NOBLANKS | LIBXML_NSCLEAN);

        $svgNodes = $dom->getElementsByTagName('svg');
        if ($svgNodes->length === 0) {
            libxml_clear_errors();
            return $svgContent;
        }

        /** @var \DOMElement $svgElement */
        $svgElement = $svgNodes->item(0);

        // --- УДАЛЕНИЕ РАЗМЕРОВ ---
        // Удаляем width и height, чтобы размеры полностью управлялись через CSS (класс w-5 h-5)
        // При этом обязательно должен остаться viewBox, иначе SVG не будет масштабироваться!
        if ($svgElement->hasAttribute('viewBox')) {
            $svgElement->removeAttribute('width');
            $svgElement->removeAttribute('height');
        }

        // --- ОЧИСТКА ЦВЕТОВ ---
        // Рекурсивно обходим все внутренние теги (path, circle, rect и т.д.)
        $allElements = $dom->getElementsByTagName('*');
        foreach ($allElements as $element) {
            /** @var \DOMElement $element */

            // Если у элемента жестко прописан цвет fill (и это не "none"), меняем его на "currentColor"
            // "currentColor" заставляет SVG краситься в цвет родительского текста (CSS свойство color)
            if ($element->hasAttribute('fill') && strtolower($element->getAttribute('fill')) !== 'none') {
                $element->setAttribute('fill', 'currentColor');
            }

            // Аналогично для stroke (контуров)
            if ($element->hasAttribute('stroke') && strtolower($element->getAttribute('stroke')) !== 'none') {
                $element->setAttribute('stroke', 'currentColor');
            }
        }

        // Сохраняем очищенный XML обратно в строку
        $cleanXml = $dom->saveXML($dom->documentElement);

        libxml_clear_errors();

        return $cleanXml ?: $svgContent;
    }
}
