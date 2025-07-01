<?php

use Sys\Console\Commands\ListCommands;
use Sys\Console\Commands\ClearCache;
use Sys\Console\Commands\ClearLog;
use Sys\Console\Commands\ClearSess;
use Sys\Console\Commands\MyUnit;
use Sys\Console\Commands\Routes;
use Sys\Console\Commands\ShowLog;
use Sys\Console\Commands\Tables;
use Sys\Create\Commands\CreateApi;
use Sys\Create\Commands\CreateDB;
use Sys\Create\Commands\CreateFiles;
use Sys\Create\Commands\CreateMvc;
use Sys\Cron\Command\CreateTask;
use Sys\Cron\Command\ShowTasks;
use Sys\Cron\Cron;
use Sys\Cron\CronStop;
use Sys\Cron\Worker\QueueWorker;
use Sys\Cron\Worker\TaskWorker;
use Sys\Fake\FakeSeedCommand;
use Sys\Fake\FakeShowCommand;
use Sys\Migrations\Commands\CreateMigrationCommand;
use Sys\Migrations\Commands\CreateTableCommand;
use Sys\Migrations\Commands\Show as ShowMigrations;
use Sys\Migrations\Commands\UpDown;

return [
    'list' => ListCommands::class,
    'clear:log' => ClearLog::class,
    'clear:cache' => ClearCache::class,
    'clear:sess' => ClearSess::class,
    'create:files' => CreateFiles::class,
    'create:mvc' => CreateMvc::class,
    'create:api' => CreateApi::class,
    'create:table' => CreateTableCommand::class,
    'create:migration' => CreateMigrationCommand::class,
    'create:db' => CreateDB::class,
    'create:task' => CreateTask::class,
    'cron:start' => Cron::class,
    'cron:stop' => CronStop::class,
    'process:queue' => QueueWorker::class,
    'process:task' => TaskWorker::class,
    'do:migrate' => UpDown::class,
    'do:test' => MyUnit::class,
    'show:migrations' => ShowMigrations::class,
    'show:mgrt' => ShowMigrations::class,
    'show:routes' => Routes::class,
    'show:tables' => Tables::class,
    'show:tasks' => ShowTasks::class,
    'show:log' => ShowLog::class,
    'show:fake' => FakeShowCommand::class,
    'seed:fake' => FakeSeedCommand::class,
    // 'debug:test' => Test::class,
];
