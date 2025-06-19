<?php declare(strict_types=1);

namespace Sys\Pipeline;

use Psr\Http\Message\ResponseInterface;

interface PostProccessHandlerInterface
{
    public function handle(ResponseInterface $response): ResponseInterface;
}
