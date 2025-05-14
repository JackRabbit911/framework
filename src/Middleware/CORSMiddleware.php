<?php

declare(strict_types=1);

namespace Sys\Middleware;

use Attribute;
use HttpSoft\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Response\ResponseHeader;

#[Attribute]
class CORSMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $contract = config('api_contracts');
        define('API_ALLOW_METHODS', $contract['methods']);

        $headers = $this->getHeaders($request, $contract);
        ResponseHeader::addHeaders($headers);

        if ($request->getMethod() === 'OPTIONS') {
            return new EmptyResponse(204);
        }

        return $handler->handle($request);
    }

    public function getHeaders($request, $contract)
    {
        $allow_headers = implode(',', $contract['headers']);
        $allow_methods = array_map(fn($v) => strtoupper($v), $contract['methods']);

        $method = $request->getMethod();
        $method = (in_array($method, $allow_methods)) ? $method : null;

        $headers = [
            'Access-Control-Allow-Headers' => $allow_headers,
            'Access-Control-Expose-Headers' => $allow_headers,
        ];

        if (in_array($request->getHeaderLine('Origin'), $contract['hosts'])) {
            $headers['Access-Control-Allow-Origin'] = $request->getHeaderLine('Origin');
        }

        if (isset($method)) {
            $headers['Access-Control-Allow-Methods'] = $method;
        }

        if (isset($contract['max_age'])) {
            $headers['Access-Control-Max-Age'] = $contract['max_age'];
        }

        if (isset($contract['allow_credentials']) && $contract['allow_credentials'] === true) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        return $headers;
    }
}
