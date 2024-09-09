<?php

namespace Sys\Controller;

use Az\Session\SessionInterface;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\RedirectResponse;
use HttpSoft\Response\TextResponse;
use HttpSoft\Response\XmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\FileResponse;
use Sys\I18n\I18n;
use Sys\Template\Template;
use Sys\Template\App;

abstract class WebController extends BaseController
{
    protected ?SessionInterface $session;
    protected ?Template $tpl;
    protected $user;
    protected ?I18n $i18n;
    protected App $app;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->session = $request->getAttribute('session');
        $this->tpl = $request->getAttribute('tpl');
        $this->user = $request->getAttribute('user');
        $this->i18n = $request->getAttribute('i18n');
        $this->app = $request->getAttribute('app');

        return parent::process($request, $handler);
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

        return $this->request->getServerParams()['HTTP_REFERER'] ?? $default;
    }
}
