<?php

declare(strict_types=1);

namespace Sys\Middleware;

use HttpSoft\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class Maintenance implements MiddlewareInterface
{
    private array $exclude_urls = [];

    public function __construct(?array $exclude_urls = null)
    {
        if (!$exclude_urls) {
            $exclude_urls = config('o2auth', 'exclude_urls');
        }

        $this->exclude_urls = $exclude_urls;
    }

    public function process(Request $request, Handler $handler): Response
    {
        $path = $request->getUri()->getPath();

        if ($this->isExclude($path)) {
            return $handler->handle($request);
        }

        $file = '../' . DOCROOT . 'maintenance';

        if (!is_file($file)) {
            return $handler->handle($request);
        }

        $user = $request->getAttribute('user');

        if ($user?->role) {
            return $handler->handle($request);
        }

        $html = file_get_contents(DOCROOT . 'maintenance.html');
        $retry = file_get_contents($file);

        $headers = [
            'Retry-After' => $retry,
        ];

        return new HtmlResponse($html, 503, $headers);
    }

    private function isExclude($path)
    {
        foreach ($this->exclude_urls as $url) {
            if (str_starts_with($path, $url)) {
                return true;
            }
        }

        return false;
    }
}
