<?php declare(strict_types=1);

namespace Sys\Service;

use Psr\Http\Message\ResponseInterface;
use Sys\PostProcess\PostProccessHandlerInterface;

class Robots implements PostProccessHandlerInterface
{
    public function handle(ResponseInterface $response): ResponseInterface
    {
        if (ENV > PRODUCTION) {
            $response = $response
                ->withAddedHeader('X-Robots-Tag', 'noindex, nofollow, noimageindex');
        }

        return $response;
    }
}
