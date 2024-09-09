<?php

namespace Sys\Cron\Command;

use Sys\Console\CallApi;
use Sys\Console\Command;
use Sys\Cron\Model\ModelTask;

final class ShowTasks extends Command
{
    public function execute()
    {
        $data = (new CallApi(ModelTask::class, 'get'))->execute();
        $this->climate->table($data);
    }
}
