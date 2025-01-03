<?php

namespace Sys\Controller;

trait InvokeTrait
{
    private function call(string $action, array $attr = [])
    {
        $container = container();

        if ($container && method_exists($container, 'call')) {
            return $container->call([$this, $action], $attr);
        }
            
        return call_user_func_array([$this, $action], $attr);
    }
}
