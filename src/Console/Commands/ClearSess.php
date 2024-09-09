<?php

namespace Sys\Console\Commands;

use App\Jobs\SessGC;
use Sys\Console\Command;
use Sys\Console\CallApi;

final class ClearSess extends Command
{
    public function execute()
    {
        $call = new CallApi(SessGC::class, '__invoke');
        $count = $call->execute();
        $this->climate->out("Deleted $count files");
    }
}
