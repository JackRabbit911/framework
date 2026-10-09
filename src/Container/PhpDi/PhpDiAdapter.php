<?php

declare(strict_types=1);

namespace Sys\Container\PhpDi;

use Sys\Container\AppContainerInterface;
use DI\Container as PhpDiContainer;

class PhpDiAdapter implements AppContainerInterface
{
    public function __construct(
        private PhpDiContainer $container
    ) {}

    public function get(string $id): mixed { return $this->container->get($id); }
    public function has(string $id): bool { return $this->container->has($id); }

    public function make(string $class, array $parameters = []): object
    {
        return $this->container->make($class, $parameters);
    }

    public function call(callable $callable, array $parameters = []): mixed
    {
        return $this->container->call($callable, $parameters);
    }
}
