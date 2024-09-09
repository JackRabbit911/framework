<?php

namespace Sys\Controller;

use Az\Route\Route;
use Sys\FileResponse;
use Sys\Helper\ResponseType;
use Sys\Exception\ExceptionResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class Media implements MiddlewareInterface
{
    private ExceptionResponseFactory $factory;

    public function __construct(ExceptionResponseFactory $factory)
    {
        $this->factory = $factory;
    }

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
            // dd($filename);
        }

        if (!is_file($filename)) {
            return $this->factory->createResponse(ResponseType::html, 404, 'File not found');
        }

        return new FileResponse($filename, (integer) $lifetime);
    }
}
