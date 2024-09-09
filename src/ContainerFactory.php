<?php

namespace Sys;

use DI\Container;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Dotenv\Dotenv;

final class ContainerFactory
{
    private $container;
    private string $mode;

    public function __construct(string $mode)
    {
        $this->mode = $mode;
    }

    public function create(ContainerBuilder $builder): Container
    {
        if ($this->container instanceof ContainerInterface) {
            return $this->container;
        }

        $builder->useAttributes(true);

        $path = CONFIG . 'container/';

        if (is_file(($common = $path . 'common.php'))) {
            $builder->addDefinitions(require_once $common);
        }

        if (is_file(($mode = $path . $this->mode . '.php'))) {
            $builder->addDefinitions(require_once $mode);
        }

        return $builder->build();
    }
}
