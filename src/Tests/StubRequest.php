<?php

declare(strict_types=1);

namespace Sys\Tests;

use HttpSoft\Message\ServerRequest;
use HttpSoft\Message\Stream;
use HttpSoft\Message\Uri;
use Psr\Http\Message\ServerRequestInterface;

class StubRequest
{
    private string $method = 'GET';

    private array $queryParams = [];

    private array $headers = [
        'Accept' => 'text/html,application/xhtml+xml',
        'Accept-Encoding' => 'gzip, deflate',
        'Accept-Language' => 'en-US,en;q=0.9',
        'Connection' => 'keep-alive',
        'Host' => 'localhost',
        'User-Agent' => 'PHPUnit/Stub',
        // 'X-Requested-With' => 'XMLHttpRequest',
    ];

    private array $serverParams = [
        'REQUEST_METHOD' => 'GET',
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'REQUEST_URI' => '/',
        'QUERY_STRING' => '',
        'SCRIPT_NAME' => '/index.php',
        'SCRIPT_FILENAME' => '/index.php',
    ];

    private mixed $parsedBody = null;

    private string $body = '';

    private array $cookies = [];

    private array $uploadedFiles = [];

    private string $uri = 'http://localhost/';

    private string $protocolVersion = '1.1';

    /**
     * @param string|null    $method      HTTP-метод
     * @param array          $queryParams  Параметры query-string
     * @param array          $headers      Заголовки
     * @param string|mixed   $body         Тело запроса (строка или массив для parsedBody)
     */
    public function __construct(
        ?string $method = null,
        array $queryParams = [],
        array $headers = [],
        $body = null
    ) {
        if ($method !== null) {
            $this->method = strtoupper($method);
            $this->serverParams['REQUEST_METHOD'] = $this->method;
        }

        if (!empty($queryParams)) {
            $this->queryParams = $queryParams;
        }

        if (!empty($headers)) {
            $this->headers = array_merge($this->headers, $headers);
        }

        if ($body !== null) {
            if (is_string($body)) {
                $this->body = $body;
            } elseif (is_array($body)) {
                $this->parsedBody = $body;
            }
        }
    }

    public function withMethod(string $method): self
    {
        $clone = clone $this;
        $clone->method = strtoupper($method);
        $clone->serverParams['REQUEST_METHOD'] = $clone->method;

        return $clone;
    }

    public function withQueryParams(array $queryParams): self
    {
        $clone = clone $this;
        $clone->queryParams = $queryParams;

        if (!empty($queryParams)) {
            $queryString = http_build_query($queryParams);
            $clone->serverParams['QUERY_STRING'] = $queryString;
            $clone->uri .= (str_contains($clone->uri, '?')) ? '&' . $queryString : '?' . $queryString;
        }

        return $clone;
    }

    public function withHeaders(array $headers): self
    {
        $clone = clone $this;
        $clone->headers = array_merge($clone->headers, $headers);

        foreach ($headers as $name => $value) {
            $serverKey = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
            $clone->serverParams[$serverKey] = $value;
        }

        return $clone;
    }

    public function withHeader(string $name, string $value): self
    {
        return $this->withHeaders([$name => $value]);
    }

    public function withBody(string $body): self
    {
        $clone = clone $this;
        $clone->body = $body;

        return $clone;
    }

    public function withParsedBody(array $parsedBody): self
    {
        $clone = clone $this;
        $clone->parsedBody = $parsedBody;

        return $clone;
    }

    public function withCookies(array $cookies): self
    {
        $clone = clone $this;
        $clone->cookies = $cookies;

        return $clone;
    }

    public function withUri(string $uri): self
    {
        $clone = clone $this;
        $clone->uri = $uri;

        $parsed = parse_url($uri);

        if (isset($parsed['path'])) {
            $clone->serverParams['REQUEST_URI'] = $parsed['path'];
        }
        if (isset($parsed['query'])) {
            $clone->serverParams['QUERY_STRING'] = $parsed['query'];
            parse_str($parsed['query'], $clone->queryParams);
        }
        if (isset($parsed['host'])) {
            $clone->headers['Host'] = $parsed['host'];
            $clone->serverParams['HTTP_HOST'] = $parsed['host'];
        }
        if (isset($parsed['scheme'])) {
            $clone->serverParams['REQUEST_SCHEME'] = $parsed['scheme'];
        }

        return $clone;
    }

    public function withServerParams(array $serverParams): self
    {
        $clone = clone $this;
        $clone->serverParams = array_merge($clone->serverParams, $serverParams);

        if (isset($serverParams['REQUEST_METHOD'])) {
            $clone->method = strtoupper($serverParams['REQUEST_METHOD']);
        }

        return $clone;
    }

    public function withAttributes(array $attributes): self
    {
        $clone = clone $this;
        $clone->serverParams['_attributes'] = $attributes;

        return $clone;
    }

    public function withProtocolVersion(string $version): self
    {
        $clone = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    public function create(): ServerRequestInterface
    {
        $uri = new Uri($this->uri);

        $body = new Stream('php://temp', 'rw');
        if ($this->body !== '') {
            $body->write($this->body);
        }

        $request = new ServerRequest(
            $this->serverParams,
            $this->uploadedFiles,
            $this->cookies,
            $this->queryParams,
            $this->parsedBody,
            $this->method,
            $uri,
            $this->headers,
            $body,
            $this->protocolVersion
        );

        if (isset($this->serverParams['_attributes'])) {
            foreach ($this->serverParams['_attributes'] as $name => $value) {
                $request = $request->withAttribute($name, $value);
            }
        }

        return $request;
    }

    public static function get(array $queryParams = [], array $headers = []): self
    {
        return new self('GET', $queryParams, $headers);
    }

    public static function post(array $queryParams = [], array $headers = [], mixed $body = null): self
    {
        return new self('POST', $queryParams, $headers, $body);
    }

    public static function put(array $queryParams = [], array $headers = [], mixed $body = null): self
    {
        return new self('PUT', $queryParams, $headers, $body);
    }

    public static function patch(array $queryParams = [], array $headers = [], mixed $body = null): self
    {
        return new self('PATCH', $queryParams, $headers, $body);
    }

    public static function delete(array $queryParams = [], array $headers = []): self
    {
        return new self('DELETE', $queryParams, $headers);
    }

    public static function json(array $data, array $queryParams = [], array $headers = []): self
    {
        $headers = array_merge([
            'Content-Type' => 'application/json',
        ], $headers);

        return new self(
            'POST',
            $queryParams,
            $headers,
            json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)
        );
    }
}
