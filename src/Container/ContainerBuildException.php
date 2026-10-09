<?php

declare(strict_types=1);

namespace Sys\Container;

use RuntimeException;
use Throwable;

class ContainerBuildException extends RuntimeException
{
    public static function fromThrowable(Throwable $e): self
    {
        return new self(
            "Критическая ошибка при сборке DI-контейнера: " . $e->getMessage(),
            500,
            $e
        );
    }
}
