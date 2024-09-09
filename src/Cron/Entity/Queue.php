<?php

namespace Sys\Cron\Entity;

use Sys\Entity\Entity;
use Sys\Cron\Model\ModelQueue;

#[ModelQueue]
final class Queue extends Entity
{
    const READY = 0;
    const IN_PROCCESS = 1;
    const SUCCESS = 2;
    const FAILED = 3;
    const ERROR = 4;

    protected string $name;
    protected string $data;
}
