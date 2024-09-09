<?php

namespace Sys\Trait;

trait ToArray
{
    public function toArray(): array
    {
        $vars = get_object_vars($this);
        $data = $vars['_data'] ?? [];
        unset($vars['_data']);
        return $vars + $data;
    }
}
