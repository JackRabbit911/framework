<?php

namespace Sys\Profiler\Model;

interface ProfilerModelInterface
{
    public function setProfiling(): void;
    public function showProfiles(): array;
    public function set(array $data): void;
    public function get(string $uri);
}
