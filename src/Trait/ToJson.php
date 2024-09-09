<?php

namespace Sys\Trait;

use Sys\Trait\ToArray;

trait ToJson
{
    public function toJson(): string
    {
        $array = $this->toArray();
        return json_encode($array, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}
