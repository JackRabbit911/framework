<?php

declare(strict_types=1);

namespace Sys\Pipeline;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class Pipeline implements PipelineInterface
{
    private array $pipeline = [];

    public function __construct(private ContainerInterface $container) {}

    public function pipe(string|object|array $middleware, ?string $prefix = null): void
    {
        if (is_string($middleware)) {
            $middleware = $this->container->get($middleware);
        }

        $this->pipeline[] = (!$prefix || $prefix === '/') ? $middleware : $this->path($prefix, $middleware);
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $this->next($handler)->handle($request);
    }

    private function next($handler)
    {
        return new class($this->pipeline, $handler) implements RequestHandlerInterface {

            public function __construct(private $pipeline, private $handler) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if (!$middleware = array_shift($this->pipeline)) {
                    return $this->handler->handle($request);
                }

                $next = clone $this;

                return $middleware->process($request, $next);
            }
        };
    }

    private function path(string $prefix, MiddlewareInterface $middleware): MiddlewareInterface
    {
        $middleware = is_string($middleware) ? $this->container->get($middleware) : $middleware;

        return new class($prefix, $middleware) implements MiddlewareInterface {
            public function __construct(private string $prefix, private MiddlewareInterface $middleware) {}

            public function process(
                ServerRequestInterface $request,
                RequestHandlerInterface $handler
            ): ResponseInterface {
                $this->prefix = $this->normalize($this->prefix);
                $path = $this->normalize($request->getUri()->getPath());

                if ($this->prefix === '/' || stripos($path, $this->prefix) === 0) {
                    return $this->middleware->process($request, $handler);
                }

                return $handler->handle($request);
            }

            private function normalize(string $path): string
            {
                $path = '/' . trim($path, '/');
                return ($path === '/') ? '/' : $path . '/';
            }
        };
    }
}
