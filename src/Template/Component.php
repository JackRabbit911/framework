<?php

namespace Sys\Template;

use HttpSoft\Response\HtmlResponse;

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

    protected function render(): string | null
    {
        if ($this->view) {
            $tpl = container()->get(TemplateInterface::class);
            return $tpl->render($this->view, $this->data);
        }

        return '';
    }
}
