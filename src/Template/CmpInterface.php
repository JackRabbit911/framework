<?php

declare(strict_types=1);

namespace Sys\Template;

interface CmpInterface
{
    public function __toString(): string;

    public function tpl(string $view): static;

    public function data(array $data): static;
}
