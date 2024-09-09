<?php

namespace Sys\Cron\Worker;

use Sys\Console\Command;
use Monolog\Logger;

abstract class WorkerAbstract extends Command
{
    protected Logger $logger;
    protected Logger $profiler;

    public function __construct()
    {
        $this->logger = container()->make('logger', config('cron', 'logger'));
        $this->profiler = container()->make('logger', config('cron', 'profiler'));
        parent::__construct();
    }

}
