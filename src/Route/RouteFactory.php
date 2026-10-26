<?php declare(strict_types=1);

namespace Az\Route;

use ReflectionClass;
use ReflectionObject;
use ReflectionFunction;
use Closure;
use Error;

class RouteFactory
{
    private array $reflect = [];

    public function create(mixed $handler, array $tokens, array $parameters): Route
    {
        $route = new Route($handler, $tokens);
        $route->methods('get');
        $route->setParameters($parameters);
        $route->reflect($this->reflect);
        
        $this->setByAttribute($route, $this->attributes());

        return $route;
    }

    public function handler(mixed $handler, array $parameters): mixed
    {
        if (is_string($handler)) {
            if (function_exists($handler)) {
                return $handler;
            }

            $handler = str_replace('@', '::', $handler);
            $handler = explode('::', $handler);

            if (!class_exists($handler[0])) {
                throw new Error(sprintf('Class or function "%s" does not exist', $handler[0]));
            }

            $handler[1] = $handler[1] ?? $parameters['action'] ?? '__invoke';
        }

        if (is_object($handler) && !$handler instanceof Closure) {
            $h[0] = $handler;
            $h[1] = $parameters['action'] ?? '__invoke';
            $handler = $h;
        }
        
        return $handler;
    }

    public function reflect(mixed $handler)
    {
        if (is_array($handler) && method_exists($handler[0], $handler[1])) {
            $this->reflect['class'] = (is_object($handler[0])) 
                ? new ReflectionObject($handler[0])
                : new ReflectionClass($handler[0]);
            $this->reflect['method'] = $this->reflect['class']->getMethod($handler[1]);
        } elseif ($handler instanceof Closure || is_string($handler) && function_exists($handler)) {
            $this->reflect['func'] = new ReflectionFunction($handler);
        } else {
            return;
        }

        return $this->reflect;
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->reflect as $reflection) {
            $attributes = array_merge($attributes, $reflection->getAttributes(Route::class) ?? []);
        }

        return $attributes;
    }

    private function setByAttribute(Route $route, array $attributes): void
    {   
        foreach ($attributes as $attribute) {
            $arguments = $attribute->getArguments();

            foreach ($arguments as $method => $arg) {
                $route->$method($arg);
            }
        }
    }
}
