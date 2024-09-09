<?php

namespace Sys\Exception;

use Az\Route\Route;
use Sys\Exception\ExceptionResponseFactory;
use Sys\Helper\ResponseType;

final class ErrorController
{
    private ExceptionResponseFactory $factory;

    public function __construct(ExceptionResponseFactory $factory)
    {
        $this->factory = $factory;
    }

    public function __invoke($request)
    {
        $code = (integer) $request->getAttribute(Route::class)->getParameters()['code'];
        $mimeNegotiator = new MimeNegotiator($request->getHeaderLine('Accept'));
        $responseType = $mimeNegotiator->getResponseType();
        $responseType = ResponseType::from($responseType);
        return $this->factory->createResponse($responseType, $code);
    }
}
