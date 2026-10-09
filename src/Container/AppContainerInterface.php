<?php

declare(strict_types=1);

namespace Sys\Container;

use Psr\Container\ContainerInterface;

interface AppContainerInterface extends ContainerInterface
{
    public function make(string $class, array $parameters = []): object;

    public function call(callable $callable, array $parameters = []): mixed;
}
