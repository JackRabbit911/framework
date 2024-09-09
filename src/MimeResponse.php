<?php

namespace Sys;

use HttpSoft\Response\ResponseExtensionTrait;
use HttpSoft\Response\ResponseStatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

class MimeResponse implements ResponseInterface, ResponseStatusCodeInterface
{
    use ResponseExtensionTrait;

    public function __construct(
        string $content,
        int $lifetime = 0,
        int $code = self::STATUS_OK,
        array $headers = [],
        string $protocol = '1.1',
        string $reasonPhrase = ''
    ) {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $contentType = $finfo->buffer($content);
        
        header_remove();

        $headers += [
            'Content-Type' => $contentType,
            'Content-length' => strlen($content),
            'Accept-Ranges' => 'bytes',
            'Content-Disposition' => 'inline',
            'Content-Transfer-Encoding' => 'binary',
        ];

        if ($lifetime > 0) {
            $headers['Cache-Control'] = 'private, max-age='.$lifetime;
        }

        $this->init($code, $reasonPhrase, $headers, $this->createBody($content), $protocol);
    }
}
