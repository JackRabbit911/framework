<?php

namespace Sys\I18n;

use Sys\I18n\Enum\DetectionMethod;
use Sys\I18n\Enum\Redirect;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use HttpSoft\Response\RedirectResponse;

final class I18nMiddleware implements MiddlewareInterface
{
    private I18n $i18n;

    public function __construct(I18n $i18n)
    {
        $this->i18n = $i18n;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->i18n->detectionMethod === DetectionMethod::Segment) {
            $base_lang = $this->i18n->baseLang();
            $lang = $this->i18n->lang();
            $uri = $request->getUri();
            $path = $uri->getPath();

            $new_path = rtrim(str_replace("/$lang/", '/', $path . '/'), '/');

            if ($new_path === '') {
                $new_path .= '/';
            }

            if ($lang === $base_lang) {
                if ($this->i18n->redirect === Redirect::Lang2empty
                && str_contains($path . '/', "/$lang/")) {
                    return new RedirectResponse($new_path);
                }

                if ($this->i18n->redirect === Redirect::Empty2lang) {
                    $this->i18n->needInsertSegment();

                    if (!str_contains($path . '/', "/$lang/")) {
                        return new RedirectResponse($this->i18n->path($path));
                    }                      
                }

                if ($this->i18n->redirect === Redirect::None
                && str_contains($path . '/', "/$lang/")) {
                    $this->i18n->needInsertSegment();
                }
            } else {
                $this->i18n->needInsertSegment();
            }

            $request = $request->withUri($uri->withPath($new_path));
        }

        return $handler->handle($request->withAttribute('i18n', $this->i18n));
    }
}
