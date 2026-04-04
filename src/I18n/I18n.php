<?php

declare(strict_types=1);

namespace Sys\I18n;

use Sys\I18n\Model\I18nModelInterface;
use Sys\Trait\Options;
use Sys\I18n\Enum\DetectionMethod;
use Sys\I18n\Enum\Redirect;
use Psr\Http\Message\ServerRequestInterface;

final class I18n
{
    use Options;

    public DetectionMethod $detectionMethod = DetectionMethod::None;
    public Redirect $redirect = Redirect::None;
    private string $lang;
    private array $langs = [];
    private int $index = 0;
    private bool $needInsertSegment = false;

    public function __construct(
        private ServerRequestInterface $request,
        private I18nModelInterface $model,
        mixed $config = null
    ) {
        $this->options();
        $this->lang = $this->detectLang($request);
        $this->model = $model;
    }

    public function lang(?string $lang = null): string
    {
        if ($lang) {
            $this->lang = $lang;
        }

        return $this->lang;
    }

    public function langs()
    {
        return $this->langs;
    }

    public function baseLang()
    {
        return array_key_first($this->langs) ?? 'en';
    }

    public function gettext(string $string, ?array $values = null): string
    {
        $string = $this->model->get($this->lang, $string);
        return ($values) ? strtr($string, $values) : $string;
    }

    public function getMap(array $filter, ?string $path = null)
    {
        return $this->model->getMap($this->lang, $filter, $path);
    }

    private function detectLang(ServerRequestInterface $request)
    {
        $detector = new DetectLang($this->langs, $this->index);
        return $detector->detectLang($request, $this->detectionMethod);
    }

    public function path(string $path): string
    {
        if (!$this->needInsertSegment) {
            return $path;
        }

        return $this->_path($path, $this->lang);
    }

    public function needInsertSegment()
    {
        $this->needInsertSegment = true;
    }

    public function linksList($path)
    {
        foreach ($this->langs as $lang => $title) {
            if ($this->lang != $lang) {
                $key = $this->getLangLink($path, $lang);
                $list[$key] = $title;
            }
        }

        return $list;
    }

    public function currentTitle()
    {
        return $this->langs[$this->lang];
    }

    public function addPath(string $path)
    {
        $this->model->addPath($path);
    }

    public function setPaths(string ...$paths): void
    {
        $this->model->setPaths($paths);
    }

    private function _path(string $path, string $lang): string
    {
        $arr = explode('/', trim($path, '/'));
        $arr[$this->index] = $lang;

        return '/' . rtrim(implode('/', $arr), '/');
    }

    private function getLangLink($path, $lang)
    {
        if ($this->detectionMethod === DetectionMethod::Subdomain) {
            $uri = $this->request->getUri();
            $scheme = $uri->getScheme();
            $host = $uri->getHost();
            $arrayHostItems = explode('.', $host);

            $arrayHostItems[$this->index] = $lang;
            $newHost = implode('.', $arrayHostItems);

            return $scheme . '://' . $newHost;
        } else {
            return $this->_path($path, $lang);
        }
    }
}
