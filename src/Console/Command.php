<?php

namespace Sys\Console;

use League\CLImate\CLImate;
use Psr\Log\LoggerInterface;
use stdClass;
use Throwable;

abstract class Command
{
    // const LOGPATH = APPPATH . 'storage/logs/';

    protected Argv $parser;
    protected CLImate $climate;
    protected string $help;
    protected stdClass $input;
    protected string $cliScriptName = 'console.php';

    public function __construct()
    {
        $this->parser = new Argv;
        $this->configure();

        $this->climate = new CLImate;
        $this->input = new stdClass;
    }

    public function __invoke($argv = [])
    {
        [$this->input->args, $this->input->opts] = $this->initialize($argv);

        if (isset($this->input->opts['help']))
        {
            $this->help();
            exit;
        }

        $this->interactive();

        try {
            $data = array_merge($this->input->args, ['opts' => (object) $this->input->opts]);
            container()->call([$this, 'execute'], $data);
        } catch (Throwable $e) {
            container()->get(LoggerInterface::class)
                ->error($e->getMessage() . ' ' . $e->getFile(), [$e->getLine()]);
            throw $e;
        }      
    }

    public function getHelp()
    {
        return (empty($this->help)) ? get_called_class() : $this->help;
    }

    protected function configure() {}

    protected function setHelp(string $str): self
    {
        $this->help = $str;
        return $this;
    }

    protected function addArgument($name, $desc = '', $default = null): self
    {
        $this->parser->addArgument($name, $desc, $default);
        return $this;
    }

    protected function addOption($name, $desc = '', $default = [true, false]): self
    {
        $this->parser->addOption($name, $desc, $default);
        return $this;
    }

    protected function initialize($argv = [])
    {
        return $this->parser->parse($argv);
    }

    protected function interactive()
    {
        foreach ($this->input->args as $name => $value) {
            if ($value === null) {
                $input = $this->climate->input('You need enter "' 
                    . $name . '" variable (' 
                    . $this->parser->getDescription($name) . ')');
                $this->input->args[$name] = $input->prompt();
            }
        }
    }

    private function help()
    {
        $help = $this->getHelp();
        $this->climate->out($help);

        if (!empty ($this->parser->setArgs)) {
            $this->climate->out('Arguments:');
        }

        foreach ($this->parser->setArgs as $arg)
        {
            $default = match (true) {
                is_array($arg['default']) => '[]',
                $arg['default'] === null => 'null',
                default => $arg['default'],
            };

            $this->climate->tab()->out($arg['desc'] . ' (default value: ' . $default . ')');
        }

        if (!empty ($this->parser->setOpts)) {
            $this->climate->out('Options:');
        }

        foreach ($this->parser->setOpts as $name => $opt)
        {
            $alias = array_search($name, $this->parser->aliases);
            $alias = ($alias) ? "(--$alias)" : '';

            $this->climate->tab()->out("-$name $alias, {$opt['desc']}");
        }
    }
}
