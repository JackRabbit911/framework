<?php

namespace Sys\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Route\Route;
use ReflectionAttribute;
use ReflectionMethod;
use Sys\Observer\Interface\Observer;
use Sys\PostProcess;

final class ControllerAttributeMiddleware implements MiddlewareInterface
{
    private PostProcess $postProcess;

    public function __construct(PostProcess $post_process)
    {
        $this->postProcess = $post_process;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(Route::class);
        [$controller, $method] = $route->getHandler();
        $reflect = new ReflectionMethod($controller, $method);
        $attributes = $reflect->getAttributes(Observer::class, ReflectionAttribute::IS_INSTANCEOF);

        foreach ($attributes as $attribute) {
            $listener = $attribute->newInstance();
            $listener->update($controller);
            $this->postProcess->enqueue($listener);
        }

        return $handler->handle($request);
    }
}
