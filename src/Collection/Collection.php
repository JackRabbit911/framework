<?php declare(strict_types=1);

namespace Sys\Collection;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use IteratorIterator;
use Traversable;

class Collection implements ArrayAccess, IteratorAggregate
{
    private array $items = [];

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetExists($offset): bool {
        return isset($this->items[$offset]);
    }

    public function offsetUnset($offset): void {
        unset($this->items[$offset]);
    }

    public function offsetGet($offset): mixed {
        return isset($this->items[$offset]) ? $this->items[$offset] : null;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }

    public function all()
    {
        return $this->items;
    }

    public function props($name = 'id')
    {
        $array = $this->_props($name);
        return new static($array);
    }

    public function unique()
    {
        $array = array_unique($this->items, SORT_REGULAR);
        return new static($array);
    }

    public function max($name)
    {
        $array = $this->_props($name);
        $key = array_search(max($array), $array);
        return $this->items[$key];
    }

    public function min($name)
    {
        $array = $this->_props($name);
        $key = array_search(min($array), $array);
        return $this->items[$key];
    }

    public function filter($callback)
    {
        $array = array_filter($this->items, $callback);
        return new static($array);
    }

    public function join($name, $glue = ' ', $finalGlue = '')
    {
        $array = $this->_props($name);

        if ($finalGlue === '') {
            return implode($glue, $array);
        }

        $count = count($array);

        if ($count === 0) {
            return '';
        }

        if ($count === 1) {
            return array_pop($array);
        }

        $finalItem = array_pop($array);

        return implode($glue, $array) . $finalGlue . $finalItem;
    }

    private function _props($name)
    {
        return array_map(function ($v) use ($name) {
            return $v->$name;
        }, $this->items);
    }
}
