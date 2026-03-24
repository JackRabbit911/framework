<?php

declare(strict_types=1);

namespace Sys\CSRF\Driver;

interface DriverInterface
{
    public function validate(string $token, int $user_id): bool;

    public function generate(int $user_id, int $expire): string;

    public function gc(): int;
}
