<?php

namespace Sys\Console\Commands;

use Sys\Console\Command;
use Sys\Config\Config;

class ListCommands extends Command
{
    public function execute()
    {
        $config = container()->get(Config::class);
        $commands = $config->addPath(__DIR__ . '/')
            ->enable(false)
            ->get('commands');

        foreach ($commands as $command => $class) {
            $help = $class; //(new $class)->getHelp();
            $result[] = [$command, $help];
        }

        $this->climate->table($result);
    }
}
