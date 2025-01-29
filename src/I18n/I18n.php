<?php declare(strict_types=1);

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
    private I18nModelInterface $model;
    private string $lang;
    private array $langs = [];
    private int $index = 0;
    private bool $needInsertSegment = false;

    public function __construct(ServerRequestInterface $request, ?I18nModelInterface $model = null)
    {
        $this->options();
        $this->lang = $this->detectLang($request);
        $this->model = $model ?: container()->get(I18nModelInterface::class);
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

    public function gettext(string $string, array $values = null): string
    {
        $string = $this->model->get($this->lang, $string);
        return ($values) ? strtr($string, $values) : $string;
    }

    public function detectLang(ServerRequestInterface $request)
    {
        $detector = new DetectLang($this->langs, $this->index);
        $lang = $detector->detectLang($request, $this->detectionMethod);

        dd($this->langs, $lang);

        return $lang;
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
                $key = $this->_path($path, $lang);
                $list[$key] = $title;               
            }
        }

        return $list;
    }

    public function addPath(string $path)
    {
        $this->model->addPath($path);
    }

    private function _path($path, $lang)
    {
        $arr = explode('/', trim($path, '/'));
        $arr = array_merge(array_slice($arr, 0, $this->index),
            [$lang],
            array_slice($arr, $this->index));
        return '/' . rtrim(implode ('/', $arr), '/');
    }
}
