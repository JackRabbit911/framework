<?php

declare(strict_types=1);

namespace Sys\Request;

use Sys\App;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Closure;
use Throwable;

class InternalServiceClient extends ServiceClient
{
    private ServerRequestInterface $request;

    public function __construct(
        private App $app,
        private ServerRequestFactoryInterface $serverRequestFactory,
        private ?string $url,
    ) {
        if ($url) {
            $this->url($url);
        }
    }

    public function url(string $url): self
    {
        $this->url = relativeUrl($url);
        return $this;
    }

    public function send(): ResponseInterface
    {
        $this->request = $this->serverRequestFactory->createServerRequest(
            $this->method,
            $this->url
        );

        $this->request = $this->request->withHeader("Accept", 'application/json');

        foreach ($this->headers as $name => $value) {
            $this->request = $this->request->withHeader($name, $value);
        }

        if (!empty($this->cookies)) {
            $this->request = $this->request->withCookieParams($this->cookies);
        }

        if (!empty($this->query)) {
            $this->request = $this->request->withQueryParams($this->query);
        }

        $this->request = $this->request->withAttribute('is_internal_call', true);

        try {
            $response = $this->app->run($this->request);
            return $this->formatSuccess ? ($this->formatSuccess)($response) : $response;
        } catch (Throwable $e) {
            if ($this->formatError) {
                return ($this->formatError)($e);
            }

            throw $e;
        }

    }

    public function getServerRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
