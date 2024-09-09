<?php

namespace Sys\Controller;

use Az\Route\NormalizeResponse;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Route\Route;

abstract class BaseController implements MiddlewareInterface
{
    use InvokeTrait;
    use NormalizeResponse;

    protected ServerRequestInterface $request;
    private string $action;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->request = $request;
        $route = $request->getAttribute(Route::class);
        $action = $route->getHandler()[1] 
        ?? $request->getAttribute('action') 
        ?? $route->getParameters()['action'] ?? '__invoke';

        $this->_before();
        $response = $this->call($action, $request->getAttribute(Route::class)->getParameters());
        $response = $this->normalizeResponse($request, $response);
        $this->_after($response);
        return $response;
    }

    // protected function addQuery($param, $uri = null)
    // {
    //     if ($uri) {
    //         $path = parse_url($uri, PHP_URL_PATH);
    //         $query = parse_url($uri, PHP_URL_QUERY) ?? '';
    //     } else {
    //         $uri = $this->request->getUri();
    //         $path = $uri->getPath();
    //         $query = $uri->getQuery() ?? '';
    //     }

    //     parse_str($query, $r1);

    //     if (is_string($param)) {
    //         parse_str($param, $param);
    //     }

    //     $result = array_merge($r1, $param);
    //     $query_str = http_build_query($result);

    //     return (!empty($query_str)) ? $path . '?' . $query_str : $path;
    // }

    protected function _before() {}

    protected function _after(&$response) {}
}
