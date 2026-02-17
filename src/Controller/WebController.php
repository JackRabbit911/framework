<?php

namespace Sys\Controller;

use Sys\Response\FileResponse;
use Sys\I18n\I18n;
use Sys\Template\App;
use Sys\Template\TemplateInterface;
use Sys\Contract\UserInterface;
use Az\Session\SessionInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\RedirectResponse;
use HttpSoft\Response\TextResponse;
use HttpSoft\Response\XmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class WebController extends BaseController
{
    protected ?SessionInterface $session;
    protected ?TemplateInterface $tpl;
    protected ?UserInterface $user;
    protected ?I18n $i18n;
    protected App $app;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->tpl = container()->get(TemplateInterface::class);
        $this->app = container()->get(App::class);

        $this->i18n = $request->getAttribute('i18n');

        if (isset($this->i18n)) {
            $this->tpl->addGlobal('i18n', $this->i18n);
        }

        $this->tpl->addFunction('__', function ($string, $values = null) {
            if (isset($this->i18n)) {
                return $this->i18n->gettext($string, $values);
            } else {
                return ($values) ? strtr($string, $values) : $string;
            }
        });

        $this->session = $request->getAttribute('session');
        $this->user = $request->getAttribute('user');

        $this->app->add('user', $this->user);
        $this->app->add('session', $this->session);
        $this->app->add('request', $request);
        $this->tpl->addGlobal('app', $this->app);

        return parent::handle($request);
    }

    protected function text(string $string): ResponseInterface
    {
        return new TextResponse($string);
    }

    protected function json($data): ResponseInterface
    {
        return new JsonResponse($data);
    }

    protected function xml(string $xml): ResponseInterface
    {
        return new XmlResponse($xml);
    }

    protected function redirect(string $uri, $code = RedirectResponse::STATUS_FOUND, $headers = []): ResponseInterface
    {
        return new RedirectResponse($uri, $code, $headers);
    }

    protected function file(string $file, int $lifetime = 0): ResponseInterface
    {
        return new FileResponse($file, $lifetime);
    }

    protected function html($string): ResponseInterface
    {
        return new HtmlResponse($string);
    }

    protected function referer($default = null)
    {
        if (!$default) {
            $default = url('home');
        }

        return referer($this->request, $default);
    }
}
