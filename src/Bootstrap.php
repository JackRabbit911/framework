<?php

namespace Sys;

final class Bootstrap
{
    public function __construct()
    {
        ob_start();
        register_shutdown_function([$this, 'shutDown'], 'Hey!');
    }

    public function __invoke()
    {
        
    }

    private function shutDown($arg = 'qq')
    {
        header('X-Profile: qwerty');
        echo $arg;
        ob_end_flush();
    }
}
