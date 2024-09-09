<?php

namespace Sys\Template\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Template\App;
use Sys\Template\Template;

final class WebTemplateGlobals implements MiddlewareInterface
{
    private Template $tpl;
    private App $app;

    public function __construct(Template $tpl, App $app)
    {
        $this->tpl = $tpl;
        $this->app = $app;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $i18n = $request->getAttribute('i18n');

        $this->tpl->addFunction('__', function ($string, $values = null) {
            return (isset($i18n)) ? $i18n->gettext($string, $values) : $string;
        });

        $this->app->add('user', $request->getAttribute('user'));
        $this->app->add('session', $request->getAttribute('session'));

        $this->tpl->addGlobal('app', $this->app);
        return $handler->handle($request
            ->withAttribute('tpl', $this->tpl)
            ->withAttribute('app', $this->app));
    }
}
