<?php

declare(strict_types=1);

namespace Sys;

use Sys\Console\App as Console;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

class AppFactory
{
    public static function create(): App
    {
        $container = self::getContainer();
        return $container->get(App::class);
    }

    public static function getContainer(): ContainerInterface
    {
        $builder = new ContainerBuilder();
        $builder->useAttributes(true);

        $files = [
            FRAMEWORK . 'Config/container.php',
            CONFIG . 'container/common.php',
            CONFIG . 'container/' . MODE . '.php',
        ];

        foreach ($files as $file) {
            if (is_file($file)) {
                $builder->addDefinitions($file);
            }
        }

        if (IS_CACHE) {
            $autowire_config = CONFIG . 'container/autowire.php';

            if (is_file($autowire_config)) {
                $builder->addDefinitions(CONFIG . 'container/autowire.php');
            }
            
            $builder->enableCompilation(STORAGE . 'cache');
        }

        $GLOBALS['container'] = $builder->build();

        return $GLOBALS['container'];
    }
}
