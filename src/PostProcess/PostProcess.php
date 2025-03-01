<?php

namespace Sys\PostProcess;

use Psr\Http\Message\ResponseInterface;
use Sys\Model\CommitListener;
use Sys\Profiler\Profiler;
use SplObjectStorage;
use IS_DEBUG;

class PostProcess implements PostProcessInterface
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
            $response = container()->call(Profiler::class, ['response' => $response]);
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

    public function config(array|string $config): self
    {
        if (is_string($config)) {
            $config = config($config);
        }
        
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
