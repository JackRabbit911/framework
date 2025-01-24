<?php

namespace Sys;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HttpSoft\Runner\MiddlewarePipelineInterface;
use HttpSoft\Runner\MiddlewareResolverInterface;
use HttpSoft\Emitter\EmitterInterface;
use Sys\Exception\SetErrorHandlerInterface;
use Sys\PostProcess\PostProcessInterface;

final class App
{
    private const NO_BODY_RESPONSE_CODES = [100, 101, 102, 204, 205, 304];

    private ServerRequestInterface $request;
    private MiddlewarePipelineInterface $pipeline;
    private MiddlewareResolverInterface $resolver;
    private RequestHandlerInterface $defaultHandler;
    private EmitterInterface $emitter;
    private PostProcessInterface $postProcess;

    public function __construct(
        ServerRequestInterface $request,
        MiddlewarePipelineInterface $pipeline,
        MiddlewareResolverInterface $resolver,    
        EmitterInterface $emitter,
        SetErrorHandlerInterface $setErrorHandler,
        RequestHandlerInterface $defaultHandler,
        PostProcessInterface $postProcess,
    )
    {
        $this->request = $request;
        $this->pipeline = $pipeline;
        $this->resolver = $resolver;
        $this->emitter = $emitter;
        $this->defaultHandler = $defaultHandler;
        $this->postProcess = $postProcess;
        $setErrorHandler;
    }

    public function pipe($middleware, string $path = null): void
    {
        $this->pipeline->pipe($this->resolver->resolve($middleware), $path);
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
            
        $response = $this->postProcess
            ->config('post_process')
            ->process($response);

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
