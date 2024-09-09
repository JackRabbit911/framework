<?php

namespace Sys\Migrations\Commands;

use Sys\Console\Command;
use Sys\Migrations\CreateMigration;
use Sys\Console\CallApi;
use Sys\Migrations\ModelMigrations;

class CreateTableCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('name', 'table name');
    }

    public function execute(CreateMigration $creator, $name)
    {
        $this->isTableExists($name);

        $pattern = "create-table-$name";
        $path = strstr($name, '_', true) ?: '';
        
        [$result, $filename] = $creator->create($pattern, $path);

        if ($result) {
            $this->climate->out('<light_green>Created file</light_green> ' . $filename);
        } else {
            $this->climate->to('error')->red()
                ->inline('Failed to write migration file ')
                ->out($filename);
        }
    }

    private function isTableExists($name)
    {
        $tables = (new CallApi(ModelMigrations::class, 'tables'))->execute(['name' => $name]);

        if (in_array($name, $tables)) {
            $this->climate->out("<red>WARNING</red> table <yellow>'$name'</yellow> is already exists");
            exit;
        }
    }
}
