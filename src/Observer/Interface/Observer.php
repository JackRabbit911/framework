<?php

namespace Sys\Observer\Interface;

interface Observer
{
    public function update(object|string|callable $object): self;

    public function handle();
}
