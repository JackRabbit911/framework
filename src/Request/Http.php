<?php

declare(strict_types=1);

namespace Sys\Request;

class Http
{
    public static function client(string $url): ServiceClientInterface
    {
        $host = parse_url($url, PHP_URL_HOST);
        $domain = env('APP_HOST');

        if (!$host || $host === $domain) {
            return container()->make(InternalServiceClient::class, ['url' => $url]);
        }

        return container()->make(ExternalServiceClient::class, ['url' => $url]);
    }

    public static function pool($callback)
    {
        $pool = new HttpPool();
        $callback($pool);

        return $pool->execute();
    }
}
