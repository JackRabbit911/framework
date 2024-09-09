<?php

namespace Sys\Template;

use RuntimeException;
use Twig\TwigFunction;

class Template
{
    private $engine;
    private string $ext;
    private string $path = '';

    public function __construct($engine, $ext)
    {
        $this->engine = $engine;
        $this->ext = $ext;
    }

    public function addGlobal($name, $value)
    {
        $this->engine->addGlobal($name, $value);
    }

    public function addFunction(string $name, callable $callback): void
    {
        if ($this->ext === 'twig') {
            $this->engine->addFunction(new TwigFunction($name, $callback));
        }
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function path($path)
    {
        $this->path = trim($path, '/') . '/';
    }

    public function render(string $view, array $params = []): string
    {
        $ext = pathinfo($view, PATHINFO_EXTENSION);
        $ext = (($ext)) ?: $this->ext;
        
        $view = ltrim($this->path . ltrim($view, '/'));
        return $this->engine->render($view . '.' .$ext, $params);
    }
}
