<?php

declare(strict_types=1);

namespace Sys\CSRF\Middleware;

use Sys\CSRF\Facade\Csrf;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ApiDeleteCsrf implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $user_id = $request->getAttribute('user')?->id;
        $token = $request->getHeaderLine(Csrf::getHeaderName());
        Csrf::delete($token, $user_id);

        return $handler->handle($request);
    }
}
