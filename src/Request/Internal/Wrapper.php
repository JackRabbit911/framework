<?php

declare(strict_types=1);

namespace Sys\Request\Internal;

use Exception;

class Wrapper
{
    public function __construct(private Client $client){}

    public function __call($name, $arguments)
    {
        $url = $arguments[0];
        $params = $arguments[1] ?? [];

        $response = $this->client->$name($url, $params);

        $code = $response->getStatusCode();
        if ($code >= 400 && $code < 500) {
            if (DISPLAY_ERRORS) {
                $msg = $response->getReasonPhrase() . ' (' . $url . ')';
                throw new Exception($msg, $code);
            } else {
                return '';
            }
        }

        return $response->getBody()->getContents();
    }
}
