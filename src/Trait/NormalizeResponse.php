<?php

namespace Sys\Trait;

use Az\Route\MimeNegotiator;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\TextResponse;
use HttpSoft\Response\XmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

trait NormalizeResponse
{
    private function normalizeResponse(ServerRequestInterface $request, mixed $response): ResponseInterface
    {
        if ($response instanceof ResponseInterface) {
            return $response;
        }
        
        $accept_header = $request->getHeaderLine('Accept');
        $mimeNegotiator = new MimeNegotiator($accept_header);
        $response_type = $mimeNegotiator->getResponseType();

        return match ($response_type) {
            'xml' => new XmlResponse($response),
            'text' => new TextResponse($response),
            'json' => new JsonResponse($response),
            default => new HtmlResponse($response),
        };
    }
}
