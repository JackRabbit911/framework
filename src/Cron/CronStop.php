<?php

namespace Sys\Cron;

use Sys\Console\Command;

class CronStop extends Command
{
    public function execute()
    {
        $status_file = config('cron', 'status_file');

        if (!is_writable($status_file)) {
            chmod($status_file, 0777);
        }
        
        file_put_contents($status_file, Cron::STOPPED);
    }
}
