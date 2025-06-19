<?php declare(strict_types=1);

namespace Sys\Pipeline;

use Psr\Http\Message\ResponseInterface;

interface PostProcessInterface
{
    public function process(ResponseInterface $response): ResponseInterface;

    public function enqueue(object $object): self;
}
