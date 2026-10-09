<?php

declare(strict_types=1);

namespace Sys\Container;

interface ContainerBuilderInterface
{
    public function build(array $configFiles, bool $isCache, string $cacheDir): AppContainerInterface;
}
