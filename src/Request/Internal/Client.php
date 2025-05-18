<?php

declare(strict_types=1);

namespace Sys\Request\Internal;

use Sys\Exception\HttpExceptionInterface;
use Sys\Helper\MimeNegotiator;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\TextResponse;
use HttpSoft\Runner\MiddlewarePipeline;
use HttpSoft\Runner\MiddlewarePipelineInterface;
use HttpSoft\Runner\MiddlewareResolverInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Client
{
    private MiddlewarePipelineInterface $pipeline;

    public function __construct(
        private Creator $creator,
        private MiddlewareResolverInterface $resolver
    )
    {
        $this->pipeline = new MiddlewarePipeline();
    }

    /**
    * @param string $url
    * @param array $params
    *   $params = [
    *       'headers'   => [...],
    *       'get'       => [...],
    *       'post'      => [...],
    *       'cookie'    => [...],
    *       'files'     => [...],
    *       'server'    => [...],
    *   ];
    *
    * @return ResponseInterface
    */
    public function get(string $url, array $params = []): ResponseInterface
    {
        $request = $this->creator->create('GET', $url, $params);
        return $this->run($request);
    }

    public function post(string $url, array $params = []): ResponseInterface
    {
        $request = $this->creator->create('POST', $url, $params);
        return $this->run($request);
    }

    public function delete(string $url, array $params = []): ResponseInterface
    {
        $request = $this->creator->create('DELETE', $url, $params);
        return $this->run($request);
    }

    public function patch(string $url, array $params = []): ResponseInterface
    {
        $request = $this->creator->create('PATCH', $url, $params);
        return $this->run($request);
    }

    public function put(string $url, array $params = []): ResponseInterface
    {
        $request = $this->creator->create('PUT', $url, $params);
        return $this->run($request);
    }

    private function run($request): ResponseInterface
    {
        $file = CONFIG . 'pipeline/' . MODE . '.php';
        if ($file && is_file($file)) {
            require_once $file;
        }

        $this->pipe($this->resolver->resolve(config('pipeline')));

        return $this->pipeline
                ->process($request, $this->default_handler($request));
    }

    private function pipe($middleware, ?string $path = null): void
    {
        $this->pipeline->pipe($this->resolver->resolve($middleware), $path);
    }

    private function default_handler($request)
    {
        $status_code = $request->getAttribute('status_code') ?? 404;
        $accept_header = $request->getHeaderLine('Accept');

        $mime_negotiator = new MimeNegotiator($accept_header);
        $response_type = $mime_negotiator->getResponseType();

        $reason_phrase = HttpExceptionInterface::ERROR_PHRASES[$status_code];

        $response = match ($response_type) {
            'json' => new JsonResponse([
                'error' => [['code' => $status_code, 'message' => $reason_phrase]]
            ], $status_code),
            default => new TextResponse($status_code . ' | ' . $reason_phrase, $status_code)
        };

        return new class ($response) implements RequestHandlerInterface
        {
            public function __construct(private ResponseInterface $response){}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return $this->response;
            }
        };
    }
}
