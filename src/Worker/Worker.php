<?php

declare(strict_types=1);

namespace Sys\Worker;

use Psr\Log\LoggerInterface;
use Throwable;

class Worker
{
    private string $workerScriptPath = __DIR__ . '/worker_script.php';
    private LoggerInterface $logger;
    private QueueInterface $queue;
    private array $preparedQueue = [];
    private int $waitTime = 0;

    public function __construct()
    {
        $this->logger = container()->get('WorkerLogger');
        $this->queue = container()->get(QueueInterface::class);
    }

    public function queue(array $queue = []): self
    {
        $this->preparedQueue = [];

        foreach ($queue as $item) {
            $this->preparedQueue[] = serialize($item);
        }

        return $this;
    }

    public function delay(int $wait_time)
    {
        $this->waitTime = $wait_time;
        return $this;
    }

    public function getDelay()
    {
        return $this->waitTime;
    }

    public function launch(?JobInterface $job = null) //: void
    {
        sleep($this->waitTime);

        if (empty($this->preparedQueue)) {
            $cmd = $job ? json_encode($job, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        } else {
            $data = $job ? array_merge($this->preparedQueue, [serialize($job)]) : $this->preparedQueue;
            $this->queue->push($data);
            $cmd = '';
        }

        $phpBinary = exec('command -v php') ?: exec('which php') ?: '/usr/local/bin/php';

        $command = sprintf(
            '%s %s %s > /dev/null 2>&1 &',
            // '%s %s %s >> ' . STORAGE . 'logs/worker_output.log 2>&1 &',
            escapeshellarg($phpBinary),
            escapeshellarg($this->workerScriptPath),
            escapeshellarg($cmd),
        );

        try {
            exec($command);
            // $this->logger->info('Воркер запущен в фоне. ' . $phpBinary);
        } catch (Throwable $e) {
            $this->logger->error('Ошибка запуска воркера: ' . $e->getMessage());
        }
    }
}
