<?php

declare(strict_types=1);

use Sys\AppFactory;
use Monolog\Level;
use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;
use Sys\Worker\QueueInterface;
use Sys\Worker\WorkerContext;
use Sys\Worker\Worker;

define('DOCROOT', getcwd());
define('APPPATH', dirname(DOCROOT) . '/application/');
require_once APPPATH . 'common/config/bootstrap.php';

$factory = new AppFactory();
$container = $factory->getContainer();
$logger = $container->get('WorkerLogger');
$queue = $container->get(QueueInterface::class);

if (empty($argv[1])) {
    $data = $queue->pop();
} else {
    $payload = json_decode($argv[1]);
    $task = new stdClass;
    $task->payload = $container->make($payload->job, ['payload' => (array) $payload->payload]);
    $task->id = '0000';
    $data = [$task];
}

foreach ($data as $task) {
    if (gc_enabled()) {
        gc_collect_cycles();
    }

    $job = is_object($task->payload) ? $task->payload : unserialize($task->payload);

    try {
        WorkerContext::setJob(get_class($job) . '#' . $task->id);
        $res = $job();
        $logger->info($res);
        $queue->delete($task->id);
        $error = false;
    } catch (Throwable $e) {
        $logger->error($e->getMessage() . ' ' . $e->getFile(), [$e->getLine()]);
        $error = true;

        // Проверяем текущее количество попыток из базы
        if (empty($argv[1])) {
            $currentAttempts = $queue->getAttempts($task->id);
        } else {
            exit;
        }

        if ($currentAttempts >= 2) {
            // Окончательный провал — помечаем как failed
            $queue->setStatus($task->id, 'failed');
            $logger->emergency("Задача превысила лимит попыток (3) и отправлена в failed. Вручную проверьте payload.");
            $currentAttempts = 3;
        } else {
            // Даем задаче еще один шанс — возвращаем статус pending
            // Следующий воркер (или этот же на следующем круге) попробует её снова
            $queue->setStatus($task->id, 'pending', ++$currentAttempts);
            $logger->warning("Задача возвращена в очередь для повторной попытки ($currentAttempts)");
        }
    } finally {
        WorkerContext::clear();
    }
}

if ($error && $currentAttempts < 3) {
    (new Worker())->delay(3)->launch();
}

// exit;
