<?php

declare(strict_types=1);

namespace Sys\CSRF\Middleware;

use Sys\CSRF\Facade\Csrf;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiCsrfMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $handler->handle($request);
        }

        $user = $request->getAttribute('user');
        $token = $request->getHeaderLine(Csrf::getHeaderName());
        $valid = Csrf::validate($token, $user?->id ?? null);

        return $valid
            ? $handler->handle($request)
            : new JsonResponse('Token not match', 400);
    }
}
