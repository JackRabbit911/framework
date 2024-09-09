<?php

namespace Sys\Migrations\Commands;

use Sys\Console\Command;

abstract class MigrationsCommandAbstract extends Command
{
    protected string $dir;

    public function __construct()
    {
        parent::__construct();
        $this->dir = config('common', 'migrations_dir');
    }
}
