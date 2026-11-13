<?php
declare(strict_types=1);

namespace Sys\Worker;

class WorkerContext
{
    private static string $currentJob = '';

    public static function setJob(string $jobName): void
    {
        self::$currentJob = $jobName;
    }

    public static function getJob(): string
    {
        return self::$currentJob;
    }

    public static function clear(): void
    {
        self::$currentJob = '';
    }
}
