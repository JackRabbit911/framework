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
    public const FORM = 'form';
    public const BODY = 'body';
    public const QUERY = 'query';

    private string $source;

    public function __construct(string $source = self::BODY)
    {
        $this->source = $source;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        if (in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $handler->handle($request);
        }

        $data = $this->getData($request);
        $user = $request->getAttribute('user');
        $token = $data['_csrf'] ?? $request->getHeaderLine('X-CSRF') ?? '';
        $valid = Csrf::validate($token, $user?->id ?? $data['id']);

        return $valid
            ? $handler->handle($request)
            : new JsonResponse('Token not match', 400);
    }

    private function getData(ServerRequestInterface $request)
    {
        return match ($this->source) {
            self::BODY => json_decode($request->getBody()->getContents(), true) ?? [],
            self::FORM => $request->getParsedBody(),
            self::QUERY => $request->getQueryParams(),
        };
    }
}
