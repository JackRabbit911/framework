<?php declare(strict_types=1);

namespace Sys\Middleware;

use Attribute;
use HttpSoft\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[Attribute]
class CORSMiddleware implements MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler): ResponseInterface
    {
        $contract = config('api_contracts', $request->getUri()->getPath());
        define('API_ALLOW_METHODS', $contract['methods']);

        if ($request->getMethod() === 'OPTIONS') {
            $headers = $this->getHeaders($request,$contract);

            return new EmptyResponse(204, $headers);
        }

        return $handler->handle($request);
    }

    public function getHeaders($request, $contract)
    {
        $allow_headers = implode(',', $contract['headers']);
        $allow_methods = strtoupper(implode(',', $contract['methods']));

        $origin = (in_array($request->getHeaderLine('Origin'), $contract['hosts']))
            ?  $request->getHeaderLine('Origin') : '';

            $headers = [
                'Access-Control-Allow-Origin' => $origin,
                'Access-Control-Allow-Methods' => $allow_methods,
                'Access-Control-Allow-Headers' => $allow_headers,
            ];

            if (isset($contract['max_age'])) {
                $headers['Access-Control-Max-Age'] = $contract['max_age'];
            }

            if (isset($contract['allow_credentials']) && $contract['allow_credentials'] === true)
            {
                $headers['Access-Control-Allow-Credentials'] = 'true';
            }

            return $headers;
    }
}
