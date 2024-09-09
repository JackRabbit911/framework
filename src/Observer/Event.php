<?php

namespace Sys\Observer;

use ReflectionMethod;
use ReflectionAttribute;
use Sys\Observer\Interface\Observer;
use Sys\Observer\Interface\ObserverBefore;
use Sys\Observer\Trait\EventProvider;

final class Event
{
    use EventProvider;

    private object|string $class;
    private string $method;
    private array $listeners;

    public function __construct(mixed $method, ?array $listeners = null)
    {
        [$this->class, $this->method] = getCallable($method);

        if (!$listeners) {
            $class = (is_string($this->class)) ? $this->class : get_class($this->class);
            $this->listeners = $this->getListenersFromProvider($class . '::' . $this->method);

            if (empty($this->listeners)) {
                $reflection = new ReflectionMethod($this->class, $this->method);
                $this->listeners = $reflection->getAttributes(Observer::class, ReflectionAttribute::IS_INSTANCEOF);
            }
        } else {
            $this->listeners = $listeners;
        }
    }

    public function call(array $data = []): mixed
    {
        foreach ($this->listeners as $key => $listener) {
            if ($listener instanceof ReflectionAttribute) {
                $this->listeners[$key] = $listener->newInstance();
            } elseif (is_string($listener)) {
                $this->listeners[$key] = new $listener;
            }

            if ($this->listeners[$key] instanceof ObserverBefore) {
                call([$listener, 'handle'], $data);
                unset($this->listeners[$key]);
            }
        }

        $result = call([$this->class, $this->method], $data);
        $data['_result'] = $result;

        foreach ($this->listeners as $key => $listener) {
            call([$listener, 'handle'], $data);
        }

        return $result;
    }
}
