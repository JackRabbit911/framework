<?php

namespace Sys\Console\Commands;

use Modules\Image\Repo;
use Sys\Config\Cache as CacheConfig;
use Sys\Console\Command;
use Sys\Helper\Facade\Dir;

final class ClearCache extends Command
{
    protected function configure()
    {
        $this->addArgument('func', 'Which cache will we clear?');
    }

    public function execute($func)
    {
        if (!method_exists($this, $func)) {
            $this->climate->red()->inline('WARNING! ');
            $this->climate->out("Argument <yellow>'$func'</yellow> not recognized");
            exit;
        }

        $out = call([__CLASS__, $func]);
        $out = (($out)) ?: "Cache '$func' was cleared";
        $this->climate->out($out);
    }

    public function config(CacheConfig $cacheConfig)
    {
        $cacheConfig->clearCacheFile();
    }

    public function img(Repo $repo)
    {
        $repo->clearCache();
    }
}
