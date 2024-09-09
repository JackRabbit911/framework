<?php

namespace Sys\Console\Commands;

use Sys\Console\Command;

final class MyUnit extends Command
{
    private string $unitPath = 'vendor/bin/phpunit';

    private array $commands = [
        'sys' => SYSPATH. 'vendor/az/',
        'app' => APPPATH . 'tests',
    ];

    private array $options = [
        'deprecations' => '--display-deprecations',
        'warnings' => '--display-warnings',
        'noticies' => '--display-notices',
    ];

    protected function configure()
    {
        $help = 'This command Runs unit tests with options.' . PHP_EOL 
            . 'Options: -d , -w, -n' . PHP_EOL
            . 'The argument can be a prefix to a route group';

        $this->setHelp($help)
            ->addArgument('group', 'sys - vendor/az, app - application', ['sys', 'app'])
            ->addOption(['deprecations', 'd'], '--display-deprecations')
            ->addOption(['warnings', 'w'], '--display-warnings')
            ->addOption(['noticies', 'n'], '--display-notices');
    }

    public function execute($group)
    {
        foreach ($this->input->opts as $opt => $val) {
            if ($val) {
                $array_opts[] = $this->options[$opt];
            }
        }

        $option = (!empty($array_opts)) ? ' ' . implode(' ', $array_opts) . ' ' : ' ';
        
        foreach ($group as $test_group) {
            $this->do($this->commands[$test_group], $option);
        }
    }

    private function do($command, $option)
    {
        system(SYSPATH . $this->unitPath . $option . $command);
    }
}
