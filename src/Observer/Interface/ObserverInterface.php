<?php

declare(strict_types=1);

namespace Sys\Observer\Interface;

use Sys\Pipeline\PostProccessHandlerInterface;

interface ObserverInterface extends PostProccessHandlerInterface
{
    public function update(object $object): void;
}
