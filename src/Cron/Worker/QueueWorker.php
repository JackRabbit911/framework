<?php

namespace Sys\Cron\Worker;

use Sys\Cron\Model\ModelQueue;
use Sys\Cron\Entity\Queue;
use Throwable;

class QueueWorker extends WorkerAbstract
{
    protected function configure()
    {
        $this->setHelp('This command for processing queues')
            ->addArgument('name', 'name of queue')
            ->addArgument('lasttime', 'max timestamp of created queue item');
    }

    public function execute(ModelQueue $modelQueue)
    {
        try {
            $start = microtime(true);
            $name = $this->input->args['name'];
            $lasttime = $this->input->args['lasttime'];
            $queue = $modelQueue->get($name, $lasttime);
            
            $defaultJob = config('queues', $name)[0];

            $failed = [];
            $i = 0;

            while (!empty($queue)) {
                    $i++;
                    $row = array_shift($queue);
                    $job = ($row->job) ?: $defaultJob;

                    $response = call($job, ['data' => $row->data]);
    
                    if ($response['status'] === false) {
                        $this->logger->warning($response['message'], $response['context']);
                        $failed[] = $row->id;
                    }
            }
           
            $modelQueue->updateStatus($failed, Queue::FAILED);
            $this->profiler->info("Queue: '$name' iterations: $i", [round(microtime(true) - $start, 4)]);

            unset($queue, $job, $name, $defaultJob, $row, $response);

        } catch (Throwable $e) {
            $this->logger->error($e->getMessage() . ' ' . $e->getFile(), [$e->getLine()]);
            array_unshift($queue, $row);

            foreach ($queue as $row) {
                $failed[] = $row->id;
            }

            $modelQueue->updateStatus($failed, Queue::ERROR);
            throw $e;
        }
    }
}
