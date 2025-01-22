<?php

namespace Sys\Template;

use Psr\Http\Message\ServerRequestInterface;
use Sys\SimpleRequest;

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

    public function isset($key)
    {
        return isset($this->objects[$key]);
    }

    public function cmp($class, array $attributes = [])
    {
        if (class_exists($class)) {
            return container()->make($class, $attributes);
        }

        $class = ucfirst($class);
        $files = array_merge(glob(APPPATH . '*/*/Component/*.php'), glob(APPPATH . '*/Component/*.php'));

        foreach($files as $file) {
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

    public function request()
    {
        static $simple_request;

        if ($simple_request) {
            return $simple_request;
        }

        $request = $this->objects['request']
            ?? $GLOBALS['request']
            ?? container()->get(ServerRequestInterface::class);

        $simple_request = new SimpleRequest($request);
        return $simple_request;
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

    public function robots(...$directives)
    {
        if (ENV < STAGE) {
            return '';
        }

        if (empty($directives)) {
            $directives = ['noindex', 'nofollow', 'noimageindex'];
        }

        $directives = implode(', ', $directives);

        return '<meta name="robots" content="' . $directives . '" />';
    }
}
