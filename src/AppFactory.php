<?php

declare(strict_types=1);

namespace Sys;

use Sys\Console\App as Console;
use Psr\Container\ContainerInterface;
use Sys\Container\AppContainerInterface;
use Sys\Container\ContainerBuilderInterface;
use Sys\Container\ContainerBuildException;
use Sys\Container\PhpDi\PhpDiContainerBuilder;

class AppFactory
{
    private ContainerBuilderInterface $builder;

    public function __construct(?ContainerBuilderInterface $builder = null)
    {
        $this->builder = $builder ?? new PhpDiContainerBuilder();
    }

    public function create(): App
    {
        $container = $this->getContainer();
        return $container->get(App::class);
    }

    private function getContainer(): AppContainerInterface
    {
        if (isset($GLOBALS['_container'])) {
            return $GLOBALS['_container'];
        }

        $files = [
            FRAMEWORK . 'Config/container.php',
            CONFIG . 'container/common.php',
            CONFIG . 'container/' . $GLOBALS['_MODE'] . '.php',
        ];

        try {
            $wrappedContainer = $this->builder->build($files, IS_CACHE, STORAGE . 'cache');
            $GLOBALS['_container'] = $wrappedContainer;
            
            return $GLOBALS['_container'];
        } catch (Throwable $e) {
            throw ContainerBuildException::fromThrowable($e);
        }
    }
}
