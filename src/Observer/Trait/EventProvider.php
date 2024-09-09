<?php

namespace Sys\Observer\Trait;

trait EventProvider
{
    private function getListenersFromProvider($key)
    {
        $config = config('event_provider', $key, []);

        if (!is_array($config)) {
            $config = [$config];
        }

        foreach ($config as $item) {
            if (is_array($item)) {
                $data = $item[1] ?? [];
                $item = $item[0]; 
            }

            $listeners[] = container()->make($item, $data);
        }

        return $listeners ?? [];
    }
}
