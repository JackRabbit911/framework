<?php

declare(strict_types=1);

namespace Sys\Pipeline;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Pipeline implements PipelineInterface
{
    private array $pipeline = [];

    public function __construct(private ContainerInterface $container){}

    public function pipe(string|object|array $middleware): void
    {
        if (is_array($middleware)) {
            foreach ($middleware as $item) {
                $this->pipe($item);
            }
        } elseif (is_string($middleware)) {
            $this->pipeline[] = $this->container->get($middleware);
        } else {
            $this->pipeline[] = $middleware;
        }
    }

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $defaultHandler
    ): ResponseInterface
    {
        return $this->next($defaultHandler)->handle($request);
    }

    private function next($defaultHandler)
    {
        return new class ($this->pipeline, $defaultHandler) implements RequestHandlerInterface {

            public function __construct(private $pipeline, private $defaultHandler){}

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                if (!$middleware = array_shift($this->pipeline)) {
                    return $this->defaultHandler->handle($request);
                }

                $next = clone $this;
                if (is_array($middleware)) {
                    dd($middleware);
                }
                return $middleware->process($request, $next);
            }
        };
    }
}
