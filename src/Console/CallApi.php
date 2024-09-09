<?php

namespace Sys\Console;

final class CallApi
{
    private string $classname;
    private string $method;
    private string $query;

    public function __construct(string $classname, string $method, ?array $args = null)
    {
        $this->classname = $classname;
        $this->method = $method;
        $this->query = ($args) ? '?' . http_build_query($args) : '';
    }

    public function execute(array $data = [])
    {
        $path = ltrim(str_replace('\\', '/', $this->classname), '/') . '/' . $this->method . $this->query;
        $client = new \GuzzleHttp\Client(['base_uri' => env('APP_HOST') . '/api/console/']);
        $response = $client->post($path, ['body' => json_encode($data, JSON_UNESCAPED_UNICODE)]);
        
        return json_decode($response->getBody()->getContents(), true);
    }
}
