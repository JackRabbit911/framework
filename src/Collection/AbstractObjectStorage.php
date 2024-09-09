<?php

namespace Sys\Collection;

use SplObjectStorage;

abstract class AbstractObjectStorage extends SplObjectStorage
{
    protected $type = null;

    public function attach(object $object, mixed $info = null): void
    {
        if (isset($this->type) && $object instanceof $this->type || !isset($this->type)) {
            parent::attach($object, $info);
        }
    }

    public function walk(callable $func): self
    {
        while($this->valid()) {
            $object = $this->current();
            $object = $func($object);            
            $this->next();
        }

        return $this;
    }
}
