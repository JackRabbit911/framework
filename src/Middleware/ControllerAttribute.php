<?php

namespace Sys\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Route\Route;
use HttpSoft\Runner\MiddlewarePipeline;
use HttpSoft\Runner\MiddlewarePipelineInterface;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use Sys\Observer\Interface\Listener;
use Sys\Observer\Interface\Observer;
use Sys\PostProcess;
use TypeError;

final class ControllerAttribute implements MiddlewareInterface
{
    private ContainerInterface $container;
    private MiddlewarePipelineInterface $pipeline;
    private PostProcess $postProcess;

    public function __construct(ContainerInterface $container, PostProcess $post_process)
    {
        $this->container = $container;
        $this->pipeline = new MiddlewarePipeline();
        $this->postProcess = $post_process;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);

        if (!$route) {
            return $handler->handle($request);
        }

        $routeHandler = $route->getHandler();

        if (is_array($routeHandler)) {
            [$controller, $method] = $routeHandler;
            $rc = new ReflectionClass($controller);
            $rm = $request->getAttribute('reflection_method') ?? new ReflectionMethod($controller, $method);

            $attributes = array_merge($rc->getAttributes(), $rm->getAttributes());
           
        } elseif (is_callable($routeHandler)) {
            $rf = new ReflectionFunction($routeHandler);
            $attributes = $rf->getAttributes();
        }

        foreach ($attributes as $attr) {
            if (is_a($attr->getName(), Route::class, true)) {
                continue;
            }

            try {
                $instance = $attr->newInstance();
            } catch (TypeError $e) {
                $args = $attr->getArguments();
                $args['subject'] = $controller ?? $routeHandler;
                $instance = $this->container->make($attr->getName(), $args);
            }

            $this->do($instance, $controller ?? $routeHandler);
        }
        
        return $this->pipeline->process($request, $handler);
    }

    private function do($instance, $controller)
    {
        match (true) {
            ($instance instanceof MiddlewareInterface) => $this->pipeline->pipe($instance),
            ($instance instanceof Observer) => $this->postProcess->enqueue($instance->update($controller)),
            ($instance instanceof Listener) => $this->postProcess->enqueue($instance),
        };
    }
}
