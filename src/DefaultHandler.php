<?php

namespace Sys;

use Sys\Exception\ExceptionResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Helper\MimeNegotiator;
use Sys\Helper\ResponseType;

class DefaultHandler implements RequestHandlerInterface
{
    public function __construct(
        private ExceptionResponseFactory $factory,
        private ?ResponseType $responseType = null){}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $headers = $request->getAttribute('headers');
        $status_code = $request->getAttribute('status_code') ?? 404;
        $accept_header = $request->getHeaderLine('Accept');

        if (!$this->responseType) {
            $mimeNegotiator = new MimeNegotiator($accept_header);
            $response_type = $mimeNegotiator->getResponseType();
            $response_type = ResponseType::from($response_type);
        } else {
            $response_type = $this->responseType;
        }

        $response = $this->factory->createResponse($response_type, $status_code);

        if ($headers) {
            foreach ($headers as $name => $value) {
                $response = $response->withAddedHeader($name, $value);
            }
        }

        return $response;
    }
}
