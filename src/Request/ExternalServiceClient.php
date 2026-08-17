<?php

declare(strict_types=1);

namespace Sys\Request;

use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\TextResponse;
use HttpSoft\Response\XmlResponse;
use Throwable;
use Closure;

class ExternalServiceClient implements ServiceClientInterface
{
    public $formatSuccess = null;
    public $formatError = null;

    private string $method = 'GET';
    private array $options = [
        'headers' => [
            'Accept' => 'application/json',
        ],
    ];

    public function __construct(
        private PsrHttpClientInterface $client,
        private string $url,
    ) {}

    public function url(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function method(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function headers(array $headers): self
    {
        $this->options['headers'] = array_replace($this->options['headers'], $headers);
        return $this;
    }

    public function cookies(array $cookies): self
    {
        foreach ($cookies as $name => $value) {
            $this->options['headers']['Cookie'][] = $name . '=' . $value;
        }
        return $this;
    }

    public function query(object|array|string $query): self
    {
        $this->options['query'] = $query;
        return $this;
    }

    public function send(): ResponseInterface
    {
        try {
            $response = $this->client->request($this->method, $this->url, $this->options);
            return $this->formatSuccess ? ($this->formatSuccess)($response) : $response;
        } catch (Throwable $e) {
            if ($this->formatError) {
                return ($this->formatError)($e);
            }
            
            return match ($this->options['headers']['Accept']) {
                // 'application/json' => new JsonResponse($this->defaultErrorFormat($e), $e->getCode()),
                'application/json' => new JsonResponse([
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ], $e->getCode()),
                'text/html' => throw $e,
                'text/plain' => new TextResponse('error: ' . $e->getCode() . ' ' . $e->getMessage(), $e->getCode()),
                'application/xml' => new XmlResponse($e->getMessage(), $e->getCode()),
                default => throw $e,
            };
        }
    }

    public function sendAsync(): PromiseInterface
    {
        // $this->client должен быть инстансом \GuzzleHttp\Client
        return $this->client->requestAsync($this->method, $this->url, $this->options);
    }

    public function formatSuccess(Closure $callback): self
    {
        $this->formatSuccess = $callback;
        return $this;
    }

    public function formatError(Closure $callback): self
    {
        $this->formatError = $callback;
        return $this;
    }

    protected function defaultErrorFormat(Throwable $e): array
    {
        $response = $e->getResponse();
        $body = $response->getBody()->getcontents();
        return json_decode($body, true);
    }
}
