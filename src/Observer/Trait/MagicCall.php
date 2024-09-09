<?php

namespace Sys\Observer\Trait;

use ReflectionAttribute;
use ReflectionMethod;
use Sys\Observer\Interface\Observer;
use Sys\Observer\Interface\ObserverBefore;

trait MagicCall
{
    use EventProvider;

    public function __call($name, $arguments)
    {
        $listeners = $this->getListenersFromProvider(get_called_class() . '::' . $name);

        if (!$listeners) {
            $reflection = new ReflectionMethod($this, $name);
            $attributes = $reflection->getAttributes(Observer::class, ReflectionAttribute::IS_INSTANCEOF);

            foreach ($attributes as $key => $attribute) {                
                $listeners[] = $attribute->newInstance();                   
            }
        }

        foreach ($listeners as $key => $listener) {
            if ($listener instanceof ObserverBefore) {
                call_user_func_array([$listener, 'handle'], $arguments);
                unset($listeners[$key]);
            }
        }

        $result = call_user_func_array([$this, $name], $arguments);
        $arguments[] = $result;

        foreach ($listeners as $key => $listener) {
            call_user_func_array([$listener, 'handle'], $arguments);
        }

        return $result;
    }
}
