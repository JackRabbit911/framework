<?php

namespace Sys\Template;

use HttpSoft\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;

abstract class Component
{
    protected ?string $view = null;
    protected array $data = [];
    protected ?string $js = null;

    public function __toString(): string
    {
        return $this->render($this->view);
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

    public function render(?string $view = null, ?array $data = null): string | null
    {
        if (!$view) {
            $view = $this->view;
        }

        if (!$data) {
            $data = $this->data;
        }

        if ($view) {
            $tpl = container()->get(TemplateInterface::class);
            return $tpl->render($view, $data);
        }

        return '';
    }
}
