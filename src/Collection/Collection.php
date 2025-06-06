<?php declare(strict_types=1);

namespace Sys\Collection;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use Traversable;

class Collection implements ArrayAccess, IteratorAggregate, Countable
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

    public function ids()
    {
        return $this->_props('id');
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

    public function map($callback)
    {
        $array = array_map($callback, $this->items);
        return new static($array);
    }

    public function replace($replace)
    {
        if ($replace instanceof Collection) {
            $replace = $replace->all();
        }

        $array = array_replace($this->items, $replace);
        return new static($array);
    }

    public function merge($merge)
    {
        if ($merge instanceof Collection) {
            $merge = $merge->all();
        }

        $array = array_merge($this->items, $merge);
        return new static($array);
    }

    public function getInstance($value, $name = 'id')
    {
        return array_reduce($this->items, function ($carry, $item) use ($name, $value) {
            if ($item->$name == $value) {
                $carry = $item;
            }

            return $carry;
        });
    }

    public function reduce($callback)
    {
        return array_reduce($this->items, $callback);
    }


    public function where($name, $op, $value)
    {
        $cmp = function ($v) use ($name, $op, $value) {
            $res = null;
            $prop = $v->$name;
            eval("\$res = $prop $op $value;");
            return $res;
        };

        $array = array_filter($this->items, $cmp);
        return new static($array);
    }

    public function first()
    {
        $key = array_key_first($this->items);
        return $this->items[$key] ?? null;
    }

    public function last()
    {
        $key = array_key_last($this->items);
        return $this->items[$key] ?? null;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function orderBy($name, $order = 'ASC')
    {
        $cmp = function ($a, $b) use ($name, $order) {
            return (strtolower($order) === 'asc') 
            ? $a->$name <=> $b->$name : $b->$name <=> $a->$name;
        };

        $array = $this->items;
        usort($array, $cmp);
        return new static($array);
    }

    public function usort($callback)
    {
        $array = $this->items;
        usort($array, $callback);
        return new static($array);
    }

    public function in($value)
    {
        return in_array($value, $this->items);
    }

    public function has($value, $name = 'id')
    {
        foreach ($this->items as $item) {
            if ($item->$name == $value) {
                return true;
            }
        }

        return false;
    }

    public function push($value)
    {
        array_push($this->items, $value);
        return new static($this->items);
    }

    public function pop()
    {
        return array_pop($this->items);
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

    public function empty()
    {
        return empty($this->items);
    }

    public function intersect($array)
    {
        if ($array instanceof Collection) {
            $ids = $array->ids();
        } else {
            $ids = array_map(function ($v) {
                return $v->id;
            }, $array);
        }

        $intersect = array_intersect($this->ids(), $ids);

        foreach ($intersect as $key => $value) {
            $result[] = $this->items[$key]; 
        }

        return new static($result ?? []);
    }

    private function _props($name)
    {
        return array_map(function ($v) use ($name) {
            return $v->$name ?? null;
        }, $this->items);
    }
}
