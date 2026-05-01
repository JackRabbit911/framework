<?php

declare(strict_types=1);

namespace Sys\CSRF;

use Sys\CSRF\Driver\DriverInterface;
use Sys\Trait\Options;

class Csrf
{
    use Options;

    private string $cookieName = 'XSRF-TOKEN';
    private string $headerName = 'X-XSRF-TOKEN';
    private int $lifetime = 7200;

    public function __construct(private DriverInterface $driver)
    {
        $this->options();
    }

    public function generate(?int $user_id, ?int $lifetime = null): string
    {
        if (!$lifetime) {
            $lifetime = $this->lifetime;
        }

        return $this->driver->generate($user_id, time() + $lifetime);
    }

    public function send(?int $user_id, ?int $lifetime = null)
    {
        if (!$lifetime) {
            $lifetime = $this->lifetime;
        }

        $expire = time() + $lifetime;

        $options = [
            'expires' => $expire,
            'path' => '/',
            'httponly' => false,
        ];

        $token = $this->generate($user_id, $expire);
        setcookie($this->cookieName, $token, $options);
    }

    public function validate(string $token, ?int $user_id): bool
    {
        return $this->driver->validate($token, $user_id);
    }

    public function delete(string $token, int $user_id)
    {
        $this->driver->delete($token, $user_id);
    }

    public function gc(): int
    {
        return $this->driver->gc();
    }

    public function getHeaderName()
    {
        return $this->headerName;
    }
}
