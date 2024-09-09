<?php

namespace Sys\Cron\Worker;

use Throwable;

final class TaskWorker extends WorkerAbstract
{
    protected function configure()
    {
        $this->setHelp('This command for processing tasks')
            ->addArgument('job', 'handler for this task')
            ->addArgument('data', 'data for this task in json', '');
    }

    public function execute($job, $data)
    {
        try {
            $start = microtime(true);
            $data = json_decode($data);
            $response = call($job, ['data' => $data]);
            $status = ($response === false) ? 'Failed' : 'Ok';
            $this->profiler->info("Task: '$job' $status", [round(microtime(true) - $start, 4)]);
            // echo $response;
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage() . ' ' . $e->getFile(), [$e->getLine()]);

            if (DISPLAY_ERRORS) {
                throw $e;
            }
        }
    }
}
