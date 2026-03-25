<?php

declare(strict_types=1);

namespace Sys\CSRF;

use Sys\CSRF\Driver\DriverInterface;

class Csrf
{
    public function __construct(private DriverInterface $driver){}

    public function generate(?int $user_id, string $form, int $expire): string
    {
        return $this->driver->generate($user_id, $form, $expire);
    }

    public function validate(string $token, ?int $user_id): bool
    {
        return $this->driver->validate($token, $user_id);
    }

    public function gc(): int
    {
        return $this->driver->gc();
    }
}
