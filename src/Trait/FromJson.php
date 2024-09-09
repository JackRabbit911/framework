<?php

namespace Sys\Trait;

trait FromJson
{
    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        return self::fromArray($data);
    }
}
