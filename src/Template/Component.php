<?php

namespace Sys\Template;

use HttpSoft\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;

abstract class Component
{
    protected ?string $view = null;
    protected array $data = [];

    public function __toString(): string
    {
        return $this->render();
    }

    public function js(string $file)
    {
        $app = container()->get(App::class);
        $app->js($file);
    }

    public function tpl(string $view): static
    {
        $this->view = $view;
        return $this;
    }

    public function data(array $data): static
    {
        $this->data = array_replace_recursive($this->data, $data);
        return $this;
    }

    public function render(array $data = []): string
    {
        if ($this->view) {
            $tpl = container()->get(TemplateInterface::class);
            return $tpl->render($this->view, array_replace_recursive($this->data, $data));
        }

        return '';
    }
}
