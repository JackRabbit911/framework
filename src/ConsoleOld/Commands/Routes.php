<?php

namespace Sys\Console\Commands;

use Az\Route\RouteCollection;
use Closure;
use Sys\Console\Command;

final class Routes extends Command
{
    protected static array $names = ['debug:routes'];
    private RouteCollection $route;

    protected function configure()
    {
        $help = 'This command prints a list of routes.' . PHP_EOL 
            . 'Options: --all, --web, --api, --cli' . PHP_EOL
            . 'The argument can be a prefix to a route group';

        $this->setHelp($help)
            ->addArgument('group', 'filter by group prefix', '')
            ->addOption('all', 'prints all of routes')
            ->addOption('web', 'prints routes list for web mode')
            ->addOption('api', 'prints routes list for api mode')
            ->addOption('cli', 'prints routes list for cli mode');
    }

    public function execute(RouteCollection $routeCollection, $group)
    {
        $this->route = $routeCollection;

        $file = CONFIG . "routes/common.php";
        if (is_file($file)) {
            require_once $file;
        }

        if ($this->input->opts['all'] || $this->input->opts['web']) {
            $file = CONFIG . "routes/web.php";
            if (is_file($file)) {
                require_once $file;
            }
        }

        if ($this->input->opts['all'] || $this->input->opts['cli']) {
            $file = CONFIG . "routes/cli.php";
            if (is_file($file)) {
                require_once $file;
            }
        }

        if ($this->input->opts['all'] || $this->input->opts['api']) {
            $file = CONFIG . "routes/api.php";
            if (is_file($file)) {
                require_once $file;
            }
        }

        $group = ($group === '') ? null : '/' . ltrim($group, '/');

        foreach ($this->route->getAll($group) as $route) {

            $data[] = [
                'NAME' => $route->getName(),
                'METHODS' => $this->getMethod($route->getMethods()),
                'PATTERN' => $route->getPattern(),
                'HANDLER' => $this->getHandlerName($route->getHandler()),
            ]; 
        }

        if (isset($data)) {
            $this->climate->table($data);
        } else {
            $this->climate->out('Routes not found');
        }
        
    }

    private function getHandlerName(mixed $handler): string
    {
        if (is_string($handler)) {
            return $handler;
        } elseif (is_array($handler)) {
            return implode('::', $handler);
        } elseif ($handler instanceof Closure) {
            return 'Closure';
        } elseif (is_callable($handler)) {
            return 'Callable';
        } else {
            return 'Unknown';
        }
    }

    private function getMethod($methods)
    {
        if (count($methods) > 1) {
            if ($key = array_search('HEAD', $methods)) {
                unset($methods[$key]);
            }

            if ($key = array_search('OPTIONS', $methods)) {
                unset($methods[$key]);
            }
        } elseif (empty($methods)) {
            $methods[] = 'ANY';
        }

        return implode(' ', $methods);
    }
}
