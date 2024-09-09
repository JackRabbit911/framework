<?php

namespace Sys\Cron\Command;

use Cron\CronExpression;
use Sys\Console\CallApi;
use Sys\Console\Command;
use Sys\Cron\Model\ModelTask;

final class CreateTask extends Command
{
    protected function configure()
    {
        $this
            ->addArgument('name', 'Name of the task')
            ->addArgument('cron', 'Valid cron expression')
            ->addArgument('handler', 'Classname of the handler for this task with double backslash')
            ->addArgument('data', 'Json string with data for handler', '');
    }

    public function execute($name, $cron, $handler, $data = null)
    {
        if (empty($data)) {
            $data = null;
        }

        $res = $this->validation($cron, $handler, $data);
        
        if ($res !== true) {
            $this->climate->red()->inline('WARNING! ');
            $this->climate->out($res);
            exit;
        }

        $data = [
            'name' => $name,
            'expression' => $cron,
            'worker' => $handler,
            'data' => $data,
        ];

        (new CallApi(ModelTask::class, 'save'))->execute(['data' => $data]);

        $this->climate->out("Task '$name' was created successful");
    }

    private function validation($cron, $handler, $data)
    {
        if (!$this->cronValidation($cron)) {
            return "Cron expression: '$cron'  is invalid";
        }

        [$class, $method] = getCallable($handler);
        if (!method_exists($class, $method)) {
            return "Method '$class::$method' does not exist";
        }

        if ($data && !$this->isJson($data)) {
            return "Json string '$data' is invalid";
        }

        return true;
    }

    private function cronValidation($expr)
    {
        if (strpos($expr, '@now') === 0 
        || (strpos($expr, '@every') === 0
        && strpos($expr, 's') !== false)
        || strtotime($expr)
        || CronExpression::isValidExpression($expr)) {
            return true;
        }

        return false;
    }

    private function isJson(?string $string) {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
     }
}
