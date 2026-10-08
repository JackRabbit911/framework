<?php

declare(strict_types=1);

namespace Sys\Pipeline;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use InvalidArgumentException;

class Pipeline implements PipelineInterface
{
    private array $pipeline = [];

    public function __construct(private ContainerInterface $container) {}

    public function pipe(string|object|array $middleware, ?string $prefix = null): void
    {
        if (is_array($middleware)) {
            foreach ($middleware as $m) {
                $this->pipe($m, $prefix);
            }
            return;
        }

        $item = [
            'middleware' => $middleware,
            'prefix' => $prefix ? '/' . trim($prefix, '/') : null
        ];

        $this->pipeline[] = $item;
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        return $this->next($handler)->handle($request);
    }

    private function next($handler)
    {
        return new class($this->pipeline, $handler, $this->container) implements RequestHandlerInterface {

            public function __construct(
                private array $pipeline, 
                private $handler,
                private ContainerInterface $container
            ) {}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if (!$item = array_shift($this->pipeline)) {
                    return $this->handler->handle($request);
                }

                $middleware = $item['middleware'];
                $prefix = $item['prefix'];

                if (is_string($middleware)) {
                    $middleware = $this->container->get($middleware);
                }

                if (!$middleware instanceof MiddlewareInterface) {
                    throw new InvalidArgumentException("The object must implement MiddlewareInterface");
                }

                if ($prefix && $prefix !== '/') {
                    $path = '/' . trim($request->getUri()->getPath(), '/');
                    
                    // Проверяем строгое совпадение ИЛИ совпадение по сегменту папки (/admin/...)
                    $isMatch = ($path === $prefix) || (str_starts_with($path, $prefix . '/'));

                    if (!$isMatch) {
                        $next = clone $this;
                        return $next->handle($request);
                    }
                }

                $next = clone $this;
                return $middleware->process($request, $next);
            }
        };
    }
}
