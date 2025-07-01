<?php

namespace Sys\Create\Commands;


final class CreateMvc extends CreateFiles
{
    protected function configure()
    {
        $this
            ->addArgument('name', 'Сommon name for group files')
            ->addArgument('types', 'Types of files: controller, entity etc..', ['controller', 'model', 'view'])
            ->addOption(['interactive', 'i'], 'Interactive mode flag');
    }

    public function execute($name, $types, $opts)
    {
        parent::execute($name, $types, $opts);
    }
}
