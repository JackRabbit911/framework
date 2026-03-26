<?php

declare(strict_types=1);

namespace Sys\CSRF\Middleware;

use Sys\CSRF\Facade\Csrf;
use HttpSoft\Message\StreamFactory;
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
        $valid = Csrf::validate($token, $user?->id);

        return $valid
            ? $handler->handle($this->removeCsrf($request, $data))
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

    private function removeCsrf(ServerRequestInterface  $request, $data)
    {
        unset($data['_csrf']);

        switch ($this->source) {
            case self::BODY:
                $factory = new StreamFactory();
                $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $stream = $factory->createStream($json);
                return $request->withBody($stream);
            case self::FORM:
                return $request->withParsedBody($data);
            case self::QUERY:
                return $request->withQueryParams($data);
        }
    }
}
