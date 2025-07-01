<?php

namespace Sys\Create\Commands;


final class CreateApi extends CreateFiles
{
    protected function configure()
    {
        $this
            ->addArgument('name', 'Сommon name for group files')
            ->addArgument('types', 'Types of files: controller, entity etc..', ['controller', 'model', 'middleware'])
            ->addOption(['interactive', 'i'], 'Interactive mode flag');
    }

    public function execute($name, $types, $opts)
    {
        parent::execute($name, $types, $opts);
    }

    protected function getPaths($path, $config)
    {
        return $config[$path]['api'] ?? parent::getPaths($path, $config);
    }
}
