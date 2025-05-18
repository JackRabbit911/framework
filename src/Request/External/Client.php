<?php

declare(strict_types=1);

namespace Sys\Request\External;

use GuzzleHttp\Client as GuzzleHttpClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    private GuzzleHttpClient $client;

    public function __construct()
    {
        $this->client = new GuzzleHttpClient(['base_uri' => env('APP_URL')]);
    }

    public function get(string $url, array $params = []): ResponseInterface
    {
        return $this->client->get($url, $params);
    }

    public function post(string $url, array $params = []): ResponseInterface
    {
        return $this->client->post($url, $params);
    }

    public function delete(string $url, array $params = []): ResponseInterface
    {
        return $this->client->delete($url, $params);
    }

    public function patch(string $url, array $params = []): ResponseInterface
    {
        return $this->client->patch($url, $params);
    }

    public function put(string $url, array $params = []): ResponseInterface
    {
        return $this->client->put($url, $params);
    }
}
