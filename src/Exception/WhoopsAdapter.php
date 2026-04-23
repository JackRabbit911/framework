<?php

namespace Sys\Exception;

use Sys\Helper\MimeNegotiator;
use Sys\Helper\ResponseType;
use Sys\Exception\ExceptionResponseFactory;
use HttpSoft\Emitter\EmitterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PlainTextHandler;
use Whoops\Handler\XmlResponseHandler;
use Whoops\Util\Misc;

final class WhoopsAdapter implements SetErrorHandlerInterface
{
    public function __construct(
        ServerRequestInterface $request,
        LoggerInterface $logger,
        private EmitterInterface $emitter,
        private ExceptionResponseFactory $responseFactory
    ) {
        $accept_header = $request->getHeaderLine('Accept');
        $mimeNegotiator = new MimeNegotiator($accept_header);
        $response_type = $mimeNegotiator->getResponseType();

        $whoops = new Whoops;

        if (
            Misc::isAjaxRequest()
            || $response_type === 'json'
            || MODE === 'api'
        ) {
            $handler = new JsonResponseHandler;
        } elseif (
            Misc::isCommandLine()
            || $response_type === 'text'
        ) {
            $handler = new PlainTextHandler();
        } elseif ($response_type === 'xml') {
            $handler = new XmlResponseHandler;
        } else {
            $handler = new PrettyPageHandler;
            $this->setEditor($handler);
        }

        if (DISPLAY_ERRORS) {
            ini_set('display_errors', 1);
            $whoops->pushHandler($handler);
        } else {
            ini_set('display_errors', 0);
            if (!str_starts_with($accept_header, 'image')) {
                $this->pushHttpHandler($whoops, ResponseType::tryFrom($response_type));
            }
        }

        $this->pushLogHandler($whoops, $logger);

        $whoops->register();
    }

    private function pushHttpHandler(Whoops $whoops, ResponseType $responseType)
    {
        $whoops->pushHandler(function ($exception, $inspector, $run) use ($responseType) {
            $run->sendHttpCode(503);
            $reasonPhrase = 'Service Unavailable';
            $response = $this->responseFactory->createResponse($responseType, 503, $reasonPhrase);
            $this->emitter->emit($response);

            return \Whoops\Handler\Handler::QUIT;
        });
    }

    private function pushLogHandler(Whoops $whoops, LoggerInterface $logger)
    {
        $whoops->pushHandler(function ($exception, $inspector, $run) use ($logger) {
            $file = str_replace(realpath(ROOTPATH), '', $exception->getFile());
            $logger->error($inspector->getExceptionMessage() . ' ' . $file, [$exception->getLine()]);
        });
    }

    private function setEditor(PrettyPageHandler $handler)
    {
        $handler->setEditor(function ($file, $line) {
            $file = str_replace(env('IDE_SEARCH', ''), env('IDE_REPLACE', ''), $file);
            return $file . ':' . $line;
        });
    }
}
