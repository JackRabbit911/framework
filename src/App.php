<?php

declare(strict_types=1);

namespace Sys;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HttpSoft\Emitter\EmitterInterface;
use Psr\Http\Message\ResponseInterface;
use Sys\Exception\SetErrorHandlerInterface;
use Sys\Pipeline\PipelineInterface;
use Sys\Pipeline\PostProcessInterface;

class App
{
    private const NO_BODY_RESPONSE_CODES = [100, 101, 102, 204, 205, 304];

    public function __construct(
        private PipelineInterface $pipeline,
        private EmitterInterface $emitter,
        private SetErrorHandlerInterface $setErrorHandler,
        private RequestHandlerInterface $defaultHandler,
        private PostProcessInterface $postProcess
    ) {
    }

    public function pipe($middleware, ?string $prefix = null): void
    {
        $this->pipeline->pipe($middleware, $prefix);
    }

    public function run(?ServerRequestInterface $request = null)
    {
        if (!$request) {
            $request = container()->get(ServerRequestInterface::class);
        }

        $this->setErrorHandler->setHandler($request);

        $GLOBALS['_MODE'] = getMode($request->getUri()->getPath());

        $file = CONFIG . 'pipeline/' . $GLOBALS['_MODE'] . '.php';
        if ($file && is_file($file)) {
            require_once $file;
        }

        foreach (config('pipeline') as $mw) {
            $this->pipe($mw);
        }

        $response = $this->pipeline
            ->process($request, $this->defaultHandler);

        $response = $this->postProcess->process($response);

        $GLOBALS['_MODE'] = MODE;

        return $response;
    }

    public function emit(ResponseInterface $response): void
    {
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
