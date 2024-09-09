<?php

namespace Sys\Exception;

use Psr\Http\Message\ResponseInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\XmlResponse;
use HttpSoft\Response\TextResponse;
use Sys\Helper\ResponseType;

final class ExceptionResponseFactory implements HttpExceptionInterface
{
    private $view = SYSPATH . 'vendor/az/sys/src/Exception/views/http.php';

    public function createResponse(ResponseType $responseType, int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if ($reasonPhrase === '') {
            $reasonPhrase = HttpExceptionInterface::ERROR_PHRASES[$code] ?? 'Page not found';
        }

        switch($responseType) {
            case ResponseType::xml:
                $xml = '<?xml version="1.0" encoding="utf-8"?>';
                $xml .= '<error><code>' .$code .'</code><message>' . $reasonPhrase . '</message></error>';
                return new XmlResponse($xml, $code);
                break;
            case ResponseType::text:
                $text = $code . ' | ' . $reasonPhrase;
                return new TextResponse($text, $code);
                break;
            case ResponseType::json:
                $array = ['error' => ['code' => $code, 'message' => $reasonPhrase]];
                return (new JsonResponse($array, $code));
                break;
            default:
                $html = render($this->view, ['code' => $code, 'msg' => $reasonPhrase]);
                return new HtmlResponse($html, $code);
        }
    }
}
