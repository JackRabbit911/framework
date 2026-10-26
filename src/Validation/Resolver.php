<?php

namespace Az\Validation;

use InvalidArgumentException;

final class Resolver
{
    private ValidationHandler $defaultHandler;
    private ?object $userHandler = null;

    public $params;

    public function __construct(?string $userHandler)
    {
        $this->defaultHandler = new ValidationHandler();

        if ($userHandler) {
            $this->userHandler = new $userHandler();
        }
    }

    public function resolve($handler)
    {
        if (is_callable($handler)) {
            return $handler;
        }

        if (is_string($handler)) {
            if ($this->userHandler && method_exists($this->userHandler, $handler)) {
                return [$this->userHandler, $handler];
            }

            if ($this->defaultHandler->_is_callable($handler)) {
                return [$this->defaultHandler, $handler];
            }
        }

        if (is_array($handler)) {
            $handler = $handler[1];
        }

        throw new InvalidArgumentException(
            sprintf('Function "%s" is not callable', $handler)
        );
    }
}
