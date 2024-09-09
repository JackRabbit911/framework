<?php

namespace Sys\Cron\Model;

use Monolog\Logger;
use Sys\Entity\Entity;
use Sys\Cron\Entity\Task;
use Sys\Cron\Expression;
use Sys\Model\Interface\Saveble;
use Sys\Model\Trait\QueryBuilder;
use Sys\Model\Trait\Save;
use InvalidArgumentException;

class ModelTask implements Saveble
{
    use QueryBuilder;
    use Save {
        save as baseSave;
    }

    protected string $table = 'tasks';
    private Expression $expression;
    // private Logger $logger;

    public function __construct(Expression $expression)
    {
        $this->expression = $expression;
        // $this->logger = container()->make('logger', config('cron', 'logger'));
    }

    public function getActualTasks(int $wait_time = 20): array
    {
        static $time = 0;

        if (time() < $time + $wait_time) {
            return [];
        }

        $tasks = $this->qb->table($this->table)
            ->where($this->qb->raw('next_time <= CURRENT_TIMESTAMP'))
            ->asObject(Task::class)->get();

        foreach ($tasks as $task) {
            $next_time = $this->nextTime($task->expression);

            $this->qb->table($this->table)->where('id', '=', $task->id)
                ->update(['next_time' => $next_time]);
        }

        $time = time();
        return $tasks;
    }

    public function save(Entity|array $data): void
    {
        if (is_array($data)) {
            $data = new Task($data);
        }

        $data->next_time = $this->nextTime($data->expression);
        $this->baseSave($data);
    }

    public function update($id, $data)
    {
        return $this->qb->table($this->table)
            ->where('id', '=', $id)
            ->update($data);
    }

    public function get()
    {
        return $this->qb->table($this->table)
            ->asObject(Task::class)->get();
    }

    private function nextTime($expr)
    {
        $next_time = $this->expression->nextRunTime($expr);
        $next_time = ($next_time) ? date('Y-m-d H:i:s', $next_time) : null;

        if (!$next_time) {
            $logger = container()->make('logger', config('cron', 'logger'));
            $e = new InvalidArgumentException('Invalid cron expression: "' . $expr . '"');
            $logger->error($e->getMessage() . ' ' . $e->getFile(), [$e->getLine()]);
        }

        return $next_time;
    }
}
