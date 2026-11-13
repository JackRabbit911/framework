<?php
declare(strict_types=1);

namespace Sys\Worker;

use JsonSerializable;

abstract class AbstractJob implements JobInterface, JsonSerializable
{
    protected array $payload;

    public function __construct(array $payload = [])
    {
        $this->payload = $payload;
    }

    public function getPayload(): array { return $this->payload; }

    public function jsonSerialize(): mixed
    {
        return [
            'job' => get_class($this),
            'payload' => $this->payload,
        ];
    }
}
