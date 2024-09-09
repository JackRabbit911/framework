<?php

namespace Sys\Console\Commands;

use Sys\Console\Command;
use Sys\Console\CallApi;
use Sys\Migrations\ModelMigrations;

class Tables extends Command
{
    protected function configure()
    {
        $this->addArgument('name', 'table name or prefix', '');
    }

    public function execute($name)
    {
        $call = new CallApi(ModelMigrations::class, 'tables');
        $tables = $call->execute(['prefix' => $name]);

        $this->climate->out('Tables list:');
        $this->climate->border('=', 30);
        $this->climate->out($tables);
    }
}
