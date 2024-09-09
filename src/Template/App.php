<?php

namespace Sys\Template;

class App
{
    private array $objects = [];
    private array $scripts = [];

    public function __call($name, $arguments)
    {
        return (is_callable($this->objects[$name]))
        ? call_user_func_array($this->objects[$name], $arguments)
        : $this->objects[$name];
    }

    public function add($key, $obj)
    {
        $this->objects[$key] = $obj;
    }

    public function cmp($class, array $attributes = [])
    {
        if (class_exists($class)) {
            return container()->make($class, $attributes);
        }

        $class = ucfirst($class);

        foreach(glob(APPPATH . '**/Component/*.php') as $file) {
            if ($class === pathinfo($file, PATHINFO_FILENAME)) {
                $file = str_replace(APPPATH, '', $file);
                $arr_file = array_map(function ($f) {
                    return ucfirst(pathinfo($f, PATHINFO_FILENAME));
                }, explode('/', $file));

                $class = implode('\\', $arr_file);

                return container()->make($class, $attributes);
            }            
        }

        return null;
    }

    public function form($instance)
    {
        return $instance;
    }

    public function request($psr = false)
    {
        return request($psr);
    }

    public function js(?string $file = null)
    {
        if ($file) {
            $this->scripts[] = $file;
            return;
        }

        $scripts = array_map(fn($v) => '<script src="' . $v . '"></script>', array_unique($this->scripts));

        return implode('<br>', $scripts);
    }
}
