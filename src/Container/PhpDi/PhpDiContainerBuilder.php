<?php

declare(strict_types=1);

namespace Sys\PhpDi\Container;

use DI\ContainerBuilder as PhpDiBuilder;

class PhpDiContainerBuilder implements ContainerBuilderInterface
{
    public function build(array $configFiles, bool $isCache, string $cacheDir): AppContainerInterface
    {
        $builder = new PhpDiBuilder();
        $builder->useAttributes(true);

        foreach ($configFiles as $file) {
            if (is_file($file)) {
                $builder->addDefinitions($file);
            }
        }

        if ($isCache) {
            $autowire_config = CONFIG . 'container/autowire.php';

            if (is_file($autowire_config)) {
                $builder->addDefinitions(CONFIG . 'container/autowire.php');
            }
            
            $builder->enableCompilation($cacheDir);
        }
        
        return new PhpDiAdapter($builder->build());
    }
}
