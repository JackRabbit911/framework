<?php

declare(strict_types=1);

namespace Sys\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Closure;
use Psr\Http\Message\StreamInterface;

interface ServiceClientInterface
{
    public function method(string $method): self;

    public function headers(array $headers): self;

    public function cookies(array $cookie): self;

    public function query(array|object|string $query): self;

    public function body(array|string|StreamInterface $body): self;

    public function formatSuccess(Closure $callback): self;

    public function formatError(Closure $callback): self;
}
