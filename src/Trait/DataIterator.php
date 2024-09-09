<?php

namespace Sys\Trait;

trait DataIterator
{
    protected array $_data = [];
    
    public function current(): mixed
    {
      return current($this->_data);
    }

    public function key(): mixed
    {
      return key($this->_data);
    }

    public function next(): void
    {
      next($this->_data);
    }

    public function rewind(): void
    {
      reset($this->_data);
    }

    public function valid(): bool
    {
      return current($this->_data) !== false;
    }
}
