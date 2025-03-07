<?php declare(strict_types=1);

namespace Sys\PostProcess;

use Psr\Http\Message\ResponseInterface;

interface PostProcessInterface
{
    public function process(ResponseInterface $response): ResponseInterface;

    public function enqueue(object $object);

    public function config(array|string $config): self;
}
