<?php
declare(strict_types=1);

namespace Sys\Worker;

interface JobInterface
{
    public function getPayload(): array;
    public function __invoke();//: mixed;
}
