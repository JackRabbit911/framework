<?php

namespace Sys\Console;

use Sys\Config\Config;

class App
{
    private $handlers = [];
    private $command;

    public function __construct()
    {
        if (!empty(getenv('TZ'))) {
            date_default_timezone_set(getenv('TZ'));
        }

        global $argv;
        array_shift($argv);
        $this->command = array_shift($argv) ?? 'list';
    }

    public function run()
    {
        if (($instance = $this->getHandler($this->command))) {
            $instance = ($instance instanceof Command) ? $instance : new $instance;
            global $argv;
            $instance($argv);
        } else {
            echo "Command '$this->command' not recognized". PHP_EOL;
        }
    }

    public function add($instance)
    {
        $this->handlers[] = $instance;
        return $this;
    }

    public function getHandler($command)
    {
        foreach ($this->handlers as $handler) {
            if ($handler::hasName($command)) {
                return $handler;
            }
        }

        if (container()->has($command)) {
            return container()->get($command);
        }

        $config = container()->get(Config::class);

        $class = $config->addPath(__DIR__ . '/')
            ->enable(false)
            ->get('commands', $command);

        return container()->get($class);
    }
}
