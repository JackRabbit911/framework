<?php

namespace Sys\Migrations\Commands;

use Sys\Console\Command;
use Sys\Migrations\CreateMigration;

final class CreateMigrationCommand extends Command
{
    protected function configure()
    {
        $this->addArgument('pattern', 'filename pattern');
        $this->addArgument('path', 'path to migation file, same as table prefix', '');
    }

    public function execute(CreateMigration $creator, string $pattern, string $path)
    {
        [$result, $filename] = $creator->create($pattern, $path);

        if ($result) {
            $this->climate->out('<light_green>Created file</light_green> ' . $filename);
        } else {
            $this->climate->to('error')->red()
                ->inline('Failed to write migration file ')
                ->out($filename);
        }
    }
}
