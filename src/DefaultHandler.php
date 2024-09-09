<?php

namespace Sys;

use Sys\Exception\ExceptionResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Exception\MimeNegotiator;
use Sys\Helper\ResponseType;

class DefaultHandler implements RequestHandlerInterface
{
    private $factory;

    public function __construct(ExceptionResponseFactory $factory, ?ResponseType $responseType = null)
    {
        $this->factory = $factory;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $request->getAttribute('headers');
        $status_code = $request->getAttribute('status_code') ?? 404;
        $accept_header = $request->getHeaderLine('Accept');
        $mimeNegotiator = new MimeNegotiator($accept_header);
        $response_type = $mimeNegotiator->getResponseType();
        $response_type = ResponseType::from($response_type);

        $response = $this->factory->createResponse($response_type, $status_code);

        if ($headers) {
            foreach ($headers as $name => $value) {
                $response = $response->withAddedHeader($name, $value);
            }
        }

        return $response;
    }
}
