<?php

declare(strict_types=1);

namespace Az\Validation\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

interface IValidationMiddleware
{
    public function getResponse(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler,
        bool $check,
    ): ResponseInterface;
}
