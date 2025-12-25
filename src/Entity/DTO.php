<?php

declare(strict_types=1);

namespace Sys\Entity;

use JsonSerializable;

abstract class DTO implements JsonSerializable
{
    private $_data;

    public function __construct(array $data) {
        $this->_data = $data;
    }

    public function jsonSerialize(): mixed {
        return $this->_data;
    }

    public function __get($name)
    {
        return $this->_data[$name];
    }
}
