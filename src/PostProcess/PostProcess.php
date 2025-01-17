<?php

namespace Sys;

use Psr\Http\Message\ResponseInterface;
use SplObjectStorage;
use Sys\Model\CommitListener;
use Sys\PostProcess\PostProccessHandlerInterface;
use Sys\PostProcess\PostProccessInterface;
use Sys\Profiler\Profiler;

class PostProcess implements PostProccessInterface
{
    private SplObjectStorage $queue;

    public function __construct()
    {
        $this->queue = new SplObjectStorage;
    }

    public function process(ResponseInterface $response): ResponseInterface
    {
        $this->enqueue(container()->get(CommitListener::class));

        foreach ($this->queue as $listener) {
            $response = $this->normalize($listener)->handle($response);
        }

        if (IS_DEBUG) {
            $response = container()->call(Profiler::class, ['response' => $response, 'mode' => getMode()]);
        }

        return $response;
    }

    public function enqueue($listener)
    {
        if (is_string($listener)) {
            $listener = container()->get($listener);
        }

        if (!$this->queue->contains($listener)) {
            $this->queue->attach($listener);
        }
    }

    public function config(array $config): self
    {
        foreach ($config as $listener) {
            $this->enqueue($listener);
        }

        return $this;
    }

    private function normalize($listener)
    {
        if ($listener instanceof PostProccessHandlerInterface) {
            return $listener;
        }

        return new class($listener) implements PostProccessHandlerInterface
        {
            private object $listener;

            public function __construct($listener)
            {
                $this->listener = $listener;
            }

            public function handle(ResponseInterface $response): ResponseInterface
            {
                $this->listener->handle();
                return $response;
            }
        };
    }
}
