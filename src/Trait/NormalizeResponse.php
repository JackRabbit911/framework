<?php

namespace Sys\Trait;

use Sys\Helper\MimeNegotiator;
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
        $mimeTypes = $mimeNegotiator->getSortedMimeTypesByHeader($accept_header);

        foreach ($mimeTypes as $mimeType) {
            return match ($mimeType) {
                'text/html', '*/*' => new HtmlResponse($response),
                'text/plain' => new TextResponse($response),
                'application/json' => new JsonResponse($response),
                'application/xml', 'text/xml' => new XmlResponse($response),
            };
        }

        return new HtmlResponse($response);
    }
}
