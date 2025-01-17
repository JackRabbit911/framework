<?php declare(strict_types=1);

namespace Sys\PostProcess;

use Psr\Http\Message\ResponseInterface;

interface PostProccessHandlerInterface
{
    public function handle(ResponseInterface $response): ResponseInterface;
}
