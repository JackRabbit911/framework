<?php

declare(strict_types=1);

namespace Sys\Observer\Interface;

interface ObserverInterface
{
    public function update(string|object $object): self;

    public function handle(): void;
}
