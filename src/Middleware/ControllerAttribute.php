<?php

namespace Sys\Middleware;

use Sys\Observer\Interface\ObserverInterface;
use Sys\Pipeline\Pipeline;
use Sys\Pipeline\PostProcessInterface;
use Az\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use Error;


final class ControllerAttribute implements MiddlewareInterface
{
    private Pipeline $pipeline;

    public function __construct(
        private ContainerInterface $container,
        private PostProcessInterface $postProcess
    )
    {
        $this->pipeline = new Pipeline($container);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);

        if (!$route) {
            return $handler->handle($request);
        }
        
        $routeHandler = $route->getHandler();
        $reflect = $request->getAttribute('reflect') ?? $this->getReflect($routeHandler);
        $attributes = $this->getAttributes($reflect);

        foreach ($attributes as $attr) {
            if (is_a($attr->getName(), Route::class, true)) {
                continue;
            }

            try {
                $instance = $attr->newInstance();              
            } catch (Error $e) {
                $args = $attr->getArguments();
                $instance = $this->container->make($attr->getName(), $args);
            }

            $this->do($instance, $routeHandler[0] ?? $routeHandler);
        }
        
        return $this->pipeline->process($request, $handler);
    }

    private function do($instance, $controller)
    {
        match (true) {
            ($instance instanceof MiddlewareInterface) => $this->pipeline->pipe($instance),
            ($instance instanceof ObserverInterface) => $this->postProcess
                ->enqueue($instance)->update($controller),
            ($instance instanceof PostProcessInterface) => $this->postProcess->enqueue($instance),
        };
    }

    private function getReflect($routeHandler)
    {
        if (is_array($routeHandler)) {
            [$controller, $method] = $routeHandler;
            $reflect['class'] = new ReflectionClass($controller);
            $reflect['method'] = new ReflectionMethod($controller, $method);
        } elseif (is_callable($routeHandler)) {
            $reflect['func'] = new ReflectionFunction($routeHandler);
        }

        return $reflect;
    }

    private function getAttributes($reflect)
    {
        if (isset($reflect['func'])) {
            return $reflect['func']->getAttributes();
        }

        return array_merge($reflect['class']->getAttributes(), $reflect['method']->getAttributes());
    }
}
