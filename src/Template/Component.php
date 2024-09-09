<?php

namespace Sys\Template;

use HttpSoft\Response\HtmlResponse;

abstract class Component
{
    public function __toString()
    {
        return container()->call([$this, 'render']);
    }

    public function __invoke()
    {
        return new HtmlResponse($this);
    }
}
