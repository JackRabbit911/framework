<?php

declare(strict_types=1);

namespace Az\Validation;

interface ValidationResponseInterface
{
    public function getResponse(array $data): array;

    public function setErrorData(string $key, string|callable $handler, array $params): void;

    public function setMsgKey(string $name, string $key): void;

    public function setMessage(string $name, string $msg): void;

    public function addMsgPath(string $path): void;
}
