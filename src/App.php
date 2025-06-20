<?php

declare(strict_types=1);

namespace Sys;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HttpSoft\Emitter\EmitterInterface;
use Sys\Exception\SetErrorHandlerInterface;
use Sys\Pipeline\PipelineInterface;
use Sys\Pipeline\PostProcessInterface;

class App
{
    private const NO_BODY_RESPONSE_CODES = [100, 101, 102, 204, 205, 304];

    public function __construct(
        private ServerRequestInterface $request,
        private PipelineInterface $pipeline,
        private EmitterInterface $emitter,
        private SetErrorHandlerInterface $setErrorHandler,
        private RequestHandlerInterface $defaultHandler,
        private PostProcessInterface $postProcess
    )
    {
        $setErrorHandler;
    }

    public function pipe($middleware, ?string $path = null): void
    {
        $this->pipeline->pipe($middleware);
    }

    public function run(): void
    {
        $file = CONFIG . 'pipeline/' . MODE . '.php';
        if ($file && is_file($file)) {
            require_once $file;
        }

        $this->pipe(config('pipeline'));

        $response = $this->pipeline
            ->process($this->request, $this->defaultHandler);
            
        $response = $this->postProcess->process($response);

        $this->emitter->emit($response, $this->isResponseWithoutBody(
            (string) request()->getMethod(),
            (int) $response->getStatusCode(),
        ));
    }

    private function isResponseWithoutBody(string $requestMethod, int $responseCode): bool
    {
        return (strtoupper($requestMethod) === 'HEAD' || in_array($responseCode, self::NO_BODY_RESPONSE_CODES, true));
    }
}
