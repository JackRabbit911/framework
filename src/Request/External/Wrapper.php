<?php

declare(strict_types=1);

namespace Sys\Request\External;

class Wrapper
{
    public function __construct(private Client $client){}

    public function __call($name, $arguments)
    {
        $url = $arguments[0];
        $params = $arguments[1] ?? [];
        $response = $this->client->$name($url, $params);

        return $response->getBody()->getContents();
    }
}
