<?php

namespace Sys\Cron;

use Sys\Console\Command;
use Sys\Cron\Model\ModelQueue;
use Sys\Cron\Model\ModelTask;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

class Cron extends Command
{
    const WORKING = 1;
    const STOPPED = 0;
    const STATUSFILE = STORAGE . 'cronstatus.txt';

    public string $statusFile;
    private int $waitTimeQueue = 5;
    private int $waitTimeTasks = 20;
    private bool $debug = false;

    public function __construct()
    {
        if (($config = config('cron'))) {
            $this->waitTimeQueue = $config['wait_time_queue'] ?? $this->waitTimeQueue;
            $this->waitTimeTasks = $config['wait_time_tasks'] ?? $this->waitTimeTasks;
            $this->statusFile = $config['status_file'] ?? self::STATUSFILE;
            $this->debug = $config['debug'] ?? false;
            unset($config);
        }

        parent::__construct();
    }

    protected function configure()
    {
        $this->setHelp('This command starts a periodic process')
            ->addArgument('waittime', 'this is waiting time, default 5 seconds', $this->waitTimeQueue);

        register_shutdown_function(function () {
            file_put_contents($this->statusFile, self::STOPPED);
            echo 'i am stop ' . round(memory_get_usage()/1024/1024, 4) . ' MB ' . PHP_EOL;
        });
    }

    public function execute(ModelQueue $modelQueue, ModelTask $modelTask)
    {
        $waittime = (int)$this->input->args['waittime'];
        file_put_contents($this->statusFile, self::WORKING);
        $php = (new PhpExecutableFinder())->find();

        $processes = [];
        $i = 0;

        if (!gc_enabled()) {
            gc_enable();
        }

        while (true) {
            foreach ($processes as $k => $process) {
                if ($process->isTerminated()) {
                    if ($this->debug) {
                        echo $process->getOutput();
                        $process->clearOutput();
                        $process->clearErrorOutput();
                    }
                    unset($processes[$k]);
                }
            }

            if (empty($processses)) {
                $processes = [];
            }

            if (file_get_contents($this->statusFile) == self::STOPPED) {
                break;
            }

            [$queues, $lastTime] = $modelQueue->getNames();
            $tasks = $modelTask->getActualTasks($this->waitTimeTasks);

            if (empty($queues) && empty($tasks)) {
                $count = count($processes);

                if(gc_enabled() && $count === 0) {
                    gc_collect_cycles();
                }
                
                // if ($this->debug) 
                // echo round(memory_get_usage()/1024/1024, 4) . ' MB ' . $count, PHP_EOL;
                // echo '.';

                sleep($waittime);
            } else {
                foreach ($queues as $name) {
                    $processes[] = new Process([$php, $this->cliScriptName, 'process:queue', $name, $lastTime]);
                    $this->processManager($processes[array_key_last($processes)], config('queues', $name)[1] ?? null);
                }

                foreach ($tasks as $task) {
                    $processes[] = new Process([$php, $this->cliScriptName, 'process:task', $task->worker, $task->data]);
                    $this->processManager($processes[array_key_last($processes)], $task->timeout ?? null);
                }
            }
        }
    }

    private function processManager($process, $timeout = null)
    {        
        if ($timeout) {
            $process->setTimeout($timeout);
        }

        if ($this->debug) {
            $process->run();
        } else {
            $process->disableOutput();
            $process->start();
        }
    }
}
