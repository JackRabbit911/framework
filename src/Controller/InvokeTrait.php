<?php

namespace Sys\Controller;

trait InvokeTrait
{
    private function call(string $action, array $attr = [])
    {
        $container = container();

        if ($container && method_exists($container, 'call')) {
            $result = $container->call([$this, $action], $attr);
        } else {
            $args = [];
            $reflect = new \ReflectionMethod($this, $action);
            foreach ($reflect->getParameters() as $param) {
                $name = $param->getName();
                $args[$name] = $attr[$name] ?? $param->getDefaultValue() ?? null;
            }
            $result = $reflect->invokeArgs($this, $args);
        }

        return $result;
    }
}
