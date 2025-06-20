<?php 

declare(strict_types=1);

namespace Sys\Response;

use Sys\Pipeline\PostProccessHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseHeader implements PostProccessHandlerInterface
{
    private static array $headers = [];

    public static function addHeader($key, $value): void
    {
        $header = self::$headers[$key] ?? [];
        $header[] = $value;
        self::$headers[$key] = array_unique($header);
    }

    public static function addHeaders(array $headers): void
    {
        foreach ($headers as $key => $value) {
            self::addHeader($key, $value);
        }
    }

    public function handle(ResponseInterface $response): ResponseInterface
    {
        foreach (self::$headers  as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }

        return $response;
    }
}
