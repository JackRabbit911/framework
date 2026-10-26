<?php declare(strict_types=1);

namespace Az\Route\Middleware;

use Az\Route\Route;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteDispatch implements MiddlewareInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($route = $request->getAttribute(Route::class)) {
            $instance = $route->getInstance($this->container);
        }
        
        if (isset($instance)) {
            return ($instance instanceof MiddlewareInterface)
                    ? $instance->process($request, $handler)
                    : $instance->handle($request);
        }

        return $handler->handle($request);
    }
}
