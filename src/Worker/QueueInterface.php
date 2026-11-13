<?php
declare(strict_types=1);

namespace Sys\Worker;

interface QueueInterface
{
    public function push(array $jobs);//: void;
    public function pop(): array;
}
