<?php

namespace Sys;

use Psr\Http\Message\ResponseInterface;
use SplObjectStorage;
use Sys\Model\CommitListener;
use Sys\Profiler\Profiler;

class PostProcess
{
    private SplObjectStorage $queue;

    public function __construct()
    {
        $this->queue = new SplObjectStorage;
    }

    public function process(ResponseInterface $response, string $mode)
    {
        $this->enqueue(container()->get(CommitListener::class));

        foreach ($this->queue as $listener) {
            $listener->handle();
        }

        if (IS_DEBUG) {
            $response = container()->call(Profiler::class, ['response' => $response, 'mode' => $mode]);
        }

        return $response;
    }

    public function enqueue(object $listener)
    {
        if (!$this->queue->contains($listener)) {
            $this->queue->attach($listener);
        }
    }
}
