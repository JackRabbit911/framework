<?php

declare(strict_types=1);

namespace Sys\Request;

use Sys\App;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use HttpSoft\Message\StreamFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\StreamInterface;
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

        foreach ($this->headers as $name => $value) {
            $this->request = $this->request->withHeader($name, $value);
        }

        if (!empty($this->cookies)) {
            $this->request = $this->request->withCookieParams($this->cookies);
        }

        if (!empty($this->query)) {
            $this->request = $this->request->withQueryParams($this->query);
        }

        if ($this->body) {
            if (is_array($this->body)) {
                $this->body = json_encode($this->body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            }

            if (!$this->body instanceof StreamInterface) {
                $factory = new StreamFactory();
                $this->body = $factory->createStream($this->body);
            }

            $this->request = $this->request->withBody($this->body);
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
