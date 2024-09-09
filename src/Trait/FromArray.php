<?php

namespace Sys\Trait;

trait FromArray
{
    public static function fromArray(array $data): self
    {
        $instance = new self();

        foreach ($data as $key => $val) {
            if (property_exists($instance, $key)) {
                $instance->$key = $val;
            } else {
                $instance->_data[$key] = $val;
            }
        }

        return $instance;
    }
}
