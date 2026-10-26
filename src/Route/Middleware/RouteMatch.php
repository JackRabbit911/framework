<?php declare(strict_types=1);

namespace Az\Route\Middleware;

use Az\Route\RouterInterface;
use Az\Route\Route;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteMatch implements MiddlewareInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$route = $this->router->match($request)) {
            if (!empty($this->router->allowedMethods)) {
                $request = $request->withAttribute('status_code', 405)
                    ->withAttribute('headers', ['Allow' => $this->router->allowedMethods]);
            }

            return $handler->handle($request);
        }

        return $handler->handle($request->withAttribute(Route::class, $route));
    }
}
