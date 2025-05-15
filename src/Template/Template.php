<?php

namespace Sys\Template;

use RuntimeException;
use Twig\TwigFunction;

class Template implements TemplateInterface
{
    private $engine;
    private string $ext;
    private string $path = '';
    private array $functions = [];

    public function __construct($engine, $ext)
    {
        $this->engine = $engine;
        $this->ext = $ext;
    }

    public function addGlobal($name, $value): self
    {
        $this->engine->addGlobal($name, $value);
        return $this;
    }

    public function addFunction(string $name, callable $callback): self
    {
        $this->functions[] = $name;

        if ($this->ext === 'twig' && !in_array($name, $this->functions)) {
            $this->engine->addFunction(new TwigFunction($name, $callback));
        }

        return $this;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function path($path)
    {
        $this->path = trim($path, '/') . '/';
    }

    public function addPath($path, $namespace)
    {
        $this->engine->getLoader()->addPath($path, $namespace);
        return $this;
    }

    public function render(string $view, array $params = []): string
    {
        $ext = pathinfo($view, PATHINFO_EXTENSION);
        $ext = (($ext)) ?: $this->ext;
        
        $view = ltrim($this->path . ltrim($view, '/'));
        return $this->engine->render($view . '.' .$ext, $params);
    }
}
