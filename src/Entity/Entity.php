<?php

namespace Sys\Entity;

use Exception;
use JsonSerializable;
use Sys\Model\CommitListener;
use Sys\Trait\ToArray;

abstract class Entity implements JsonSerializable
{
    use ToArray;

    protected array $_data = [];

    public function save($model = null)
    {
        CommitListener::update($this, $model);
        return $this;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function set($key, $value)
    {
        if (property_exists($this, $key)) {
            $this->$key = $value;
        } else {
            $this->_data[$key] = $value;
        }
    }

    public function update(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                if (is_array($this->$key) && is_array($value)) {
                    $this->$key = array_replace_recursive($this->$key, $value);
                } else {
                    $this->$key = $value;
                }
            } else {
                if (isset($this->_data[$key]) && is_array($this->_data[$key]) && is_array($value)) {
                    $this->_data[$key] = array_replace_recursive($this->_data[$key], $value);
                } else {
                    $this->_data[$key] = $value;
                }
            }
        }

        return $this;
    }

    public function __toString()
    {
        return spl_object_hash($this);
    }

    public function __isset($name)
    {
        return (isset($this->$name) || isset($this->_data[$name]));
    }

    public function __unset($name)
    {
        unset($this->$name);
        unset($this->_data[$name]);
    }

    public function __set($key, $value)
    {
        $this->set($key, $value);
    }

    public function &__get($key): mixed
    {
        $null = null;

        if (property_exists($this, $key)) {
            return $this->$key;
        } elseif (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } elseif (DISPLAY_ERRORS) {
            throw new Exception(sprintf('property "%s" is not defined', $key));
        } else {
            return $null;
        }
    }

    public function __call($name, $arguments)
    {
        return $this->$name ?? $this->_data[$name] ?? null;
    }
}
