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
use Exception;

class ExternalServiceClient extends ServiceClient
{
    private array $options = [];

    public function __construct(
        private PsrHttpClientInterface $client,
        private ?string $url,
    )
    {
        if ($url) {
            $this->url($url);
        }
    }

    public function url(string $url): self
    {
        $this->url = absoluteUrl($url);
        return $this;
    }

    public function send(): ResponseInterface
    {
        if (!$this->url) {
            throw new Exception('Property $url is not defined');
        }

        if (!empty($this->headers)) {
            $options['headers'] = $this->headers;
        }

        if (!empty($this->query)) {
            $options['query'] = $this->query;
        }

        if ($this->body) {
            if (is_array($this->body)) {
                $options['json'] = $this->body;
            } else {
                $options['body'] = $this->body;
            }
        }

        $this->options = array_replace_recursive($this->options, $options);

        try {
            $response = $this->client->request($this->method, $this->url, $this->options);
            return $this->formatSuccess ? ($this->formatSuccess)($response) : $response;
        } catch (Throwable $e) {
            if ($this->formatError) {
                return ($this->formatError)($e);
            }
            
            return match ($this->options['headers']['Accept']) {
                'application/json' => new JsonResponse($this->defaultErrorFormat($e), 500),
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

    protected function defaultErrorFormat(Throwable $e): array
    {
        $response = $e->getResponse();
        $body = $response->getBody()->getContents();
        return json_decode($body, true);
    }
}
