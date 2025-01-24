<?php

namespace Sys;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

final class ContainerFactory
{
    public function create(ContainerBuilder $builder): ContainerInterface
    {
        $builder->useAttributes(true);

        $files = [
            __DIR__ . '/Config/container.php',
            CONFIG . 'container/common.php',
            CONFIG . 'container/' . MODE . '.php',
        ];

        foreach ($files as $file) {
            if (is_file($file)) {
                $builder->addDefinitions($file);
            }
        }

        if (IS_CACHE) {
            $builder->addDefinitions(CONFIG . 'container/autowire.php');
            $builder->enableCompilation(STORAGE . 'cache');
        }

        return $builder->build();
    }
}
