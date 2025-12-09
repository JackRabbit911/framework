<?php

declare(strict_types=1);

namespace Sys\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Helper\Facade\Arr;

class PreparePostData implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $post = $request->getParsedBody();
        $post = Arr::preparePost($post);

        return $handler->handle($request->withParsedBody($post));
    }
}
