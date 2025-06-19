<?php

declare(strict_types=1);

namespace Sys\Pipeline;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class PostProcess implements PostProcessInterface
{
    private array $handlers = [];

    public function __construct(private ContainerInterface $container)
    {
        foreach (config('post_process') ?? [] as $class) {
            $this->enqueue($class);
        }
    }

    public function enqueue(string|object $class): self
    {
        $handler = is_string($class) ? $this->container->get($class) : $class;

        if (!in_array($handler, $this->handlers)) {
            $this->handlers[] = $handler;
        }

        return $this;
    }

    public function process(ResponseInterface $response): ResponseInterface
    {
        if(!$handler = array_shift($this->handlers)) {
            return $response;
        }

        return $this->next($response, $handler);
    }

    private function next(ResponseInterface $response, $handler): ResponseInterface
    {
        $response = $handler->handle($response);
        return $this->process($response);
    }
}
