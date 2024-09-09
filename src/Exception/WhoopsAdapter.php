<?php

namespace Sys\Exception;

use HttpSoft\Emitter\EmitterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Whoops\Run as Whoops;
use Whoops\Handler\PrettyPageHandler;
use Sys\Helper\ResponseType;
use Sys\Exception\ExceptionResponseFactory;

final class WhoopsAdapter implements SetErrorHandlerInterface
{
    private EmitterInterface $emitter;
    private ExceptionResponseFactory $responseFactory;

    public function __construct(ServerRequestInterface $request, 
    LoggerInterface $logger, EmitterInterface $emitter, 
    ExceptionResponseFactory $response_factory)
    {
        $this->emitter = $emitter;
        $this->responseFactory = $response_factory;

        $accept_header = $request->getHeaderLine('Accept');

        $whoops = new Whoops;

        if (\Whoops\Util\Misc::isAjaxRequest() 
            || strpos($accept_header, 'application/json') === 0
            || getMode() === 'api') {
            $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
            $responseType = ResponseType::json;
        } elseif (\Whoops\Util\Misc::isCommandLine()) {
            $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
            $responseType = ResponseType::text;
        } elseif (strpos($accept_header, 'text/html') === 0) {
            $handler = new \Whoops\Handler\PrettyPageHandler;
            $this->setEditor($handler);
            $whoops->pushHandler($handler);
            $responseType = ResponseType::html;
        } else {
            $this->pushMimeHandler($whoops, $accept_header);
        }

        if (DISPLAY_ERRORS) {
            ini_set('display_errors', 1);
        } else {
            ini_set('display_errors', 0);     
            $this->pushHttpHandler($whoops, $responseType);
            
            if (env('APP_ENV') <= PRODUCTION) {
                $this->pushRollbackHandler($whoops);
            }
        }

        $this->pushLogHandler($whoops, $logger);

        $whoops->register();
    }

    private function pushMimeHandler(Whoops $whoops, string $accept_header)
    {
        $mimeNegotiator = new MimeNegotiator($accept_header);
        $responseType = $mimeNegotiator->getResponseType();

        switch ($responseType) {
            case 'json':
                $whoops->pushHandler(new \Whoops\Handler\JsonResponseHandler);
                break;
            case 'text':
                $whoops->pushHandler(new \Whoops\Handler\PlainTextHandler);
                $whoops->allowQuit(false);
                break;
            case 'xml':
                $whoops->pushHandler(new \Whoops\Handler\XmlResponseHandler);
                break;
            default:
                if (ini_get('display_errors') != 0) {
                    $handler = new PrettyPageHandler;
                    $this->setEditor($handler);
                    $whoops->pushHandler($handler);
                }
        }
    }

    private function pushHttpHandler(Whoops $whoops, ResponseType $responseType)
    {
        $whoops->pushHandler(function($exception, $inspector, $run) use ($responseType) {
            $run->sendHttpCode(503);
            $reasonPhrase = 'Service Unavailable<br>Wait a few seconds';
            $response = $this->responseFactory->createResponse($responseType, 503, $reasonPhrase);
            $this->emitter->emit($response);

            return \Whoops\Handler\Handler::QUIT;
        });
    }

    private function pushLogHandler(Whoops $whoops, LoggerInterface $logger)
    {
        $whoops->pushHandler(function ($exception, $inspector, $run) use ($logger) {
            $file = str_replace(realpath(ROOTPATH), '', $exception->getFile());
            $logger->error($inspector->getExceptionMessage().' '.$file, [$exception->getLine()]);
        });
    }

    private function pushRollbackHandler(Whoops $whoops)
    {
        $whoops->pushHandler(function ($exception, $inspector, $run) {
            try {
                rename(ROOTPATH . 'app', ROOTPATH . 'error');
                rename(ROOTPATH . 'backup', ROOTPATH . 'app');
                header("Refresh: 2");
            } catch (\ErrorException $e) {
                header("Refresh: 2");
            }
        });
    }

    private function setEditor(PrettyPageHandler $handler)
    {
        $handler->setEditor(function ($file, $line) {
            $file = str_replace(env('IDE_SEARCH'), env('IDE_REPLACE'), $file);
            return $file . ':' . $line;
        });
    }
}
