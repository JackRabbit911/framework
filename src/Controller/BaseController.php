<?php

namespace Sys\Controller;

use Sys\Trait\NormalizeResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Route\Route;

abstract class BaseController implements RequestHandlerInterface
{
    use InvokeTrait;
    use NormalizeResponse;

    protected ServerRequestInterface $request;
    protected array $parameters;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $route = $request->getAttribute(Route::class);
        $this->parameters = $route->getParameters();

        [$handler, $action] = $route->getHandler();

        $this->_before();
        $response = $this->call($action, $this->parameters);
        $response = $this->normalizeResponse($request, $response);
        $this->_after($response);
        return $response;
    }

    protected function _before() {}

    protected function _after(&$response) {}
}
