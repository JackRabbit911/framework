<?php declare(strict_types=1);

namespace Az\Route;

use Psr\Container\ContainerInterface;

class Route
{
    private $handler;
    private array $methods = [];
    private array $tokens = [];
    private ?string $host = null;
    private array $parameters = [];
    private array $filters = [];
    private ?bool $ajax = null;
    private array $reflect = [];

    public function __construct(mixed $handler, array $tokens)
    {
        $this->handler = $handler;
        $this->tokens($tokens);
    }

    public function methods(array|string $methods)
    {
        if (is_string($methods)) {
            $methods = [$methods];
        }

        $this->methods = array_map(fn ($v) => strtoupper($v), $methods);

        if (in_array('ANY', $this->methods)) {
            $this->methods = [];
        }
        
        return $this;
    }

    public function tokens(array $tokens): self
    {
        foreach ($tokens as $key => $token) {
            $this->tokens[$key] = $token;
        }

        return $this;
    }

    public function host(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function filter(callable|array|string $filter): self
    {
        $this->filters[] = $filter;
        return $this;
    }

    public function ajax(?bool $ajax = null): self
    {
        $this->ajax = $ajax;
        return $this;
    }

    public function isAjax(): ?bool
    {
        return $this->ajax;
    }

    public function getTokens(): array
    {
        return $this->tokens;
    }

    public function setParameters(array $params)
    {
        $this->parameters = array_replace($this->parameters, array_filter($params));
        unset($this->parameters['action']);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getHandler(): mixed
    {
        return $this->handler;
    }

    public function getMethods(): array
    {
        return $this->methods;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function reflect(?array $reflect = null)
    {
        if (!$reflect) {
            return $this->reflect;
        }

        $this->reflect = $reflect;
    }

    public function getInstance(?ContainerInterface $container = null): mixed
    {
        $resolver = new Resolver($container);
        return $resolver->resolve($this->handler);
    }
}
