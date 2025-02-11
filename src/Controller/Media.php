<?php

namespace Sys\Controller;

use Az\Route\Route;
use Sys\Response\FileResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Media implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $params = $request->getAttribute(Route::class)->getParameters();

        $file = $params['file'];
        $lifetime = $params['lifetime'] ?? 0;

        if (!is_file($file)) {
            $filename = STORAGE . $file;
        }

        if (!is_file($filename)) {
            $filename = SYSPATH . 'vendor/az/sys/src/' . $file;
        }

        if (!is_file($filename)) {
            $filename = APPPATH . $file;
        }

        if (!is_file($filename)) {
            return $handler->handle($request);
        }

        return new FileResponse($filename, (integer) $lifetime);
    }
}
