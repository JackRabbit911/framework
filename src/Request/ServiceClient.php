<?php
declare(strict_types=1);

namespace Sys\Request;

use Closure;

abstract class ServiceClient implements ServiceClientInterface
{
    protected $formatSuccess = null;
    protected $formatError = null;
    protected string $method = 'GET';
    protected array $headers = [];
    protected array $cookies = [];
    protected array|object|string $query;

    public function method(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function cookies(array $cookie): self
    {
        $this->cookies = $cookie;
        return $this;
    }

    public function query(array|object|string $query): self
    {
        $this->query = $query;
        return $this;
    }

    public function formatSuccess(Closure $callback): self
    {
        $this->formatSuccess = $callback;
        return $this;
    }

    public function formatError(?Closure $callback = null): self
    {
        $this->formatError = $callback;
        return $this;
    }
}
