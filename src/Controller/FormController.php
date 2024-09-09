<?php

namespace Sys\Controller;

use Sys\Controller\WebController;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Template\Form;

abstract class FormController extends WebController
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (($session = $request->getAttribute('session'))) {
            $tpl = $request->getAttribute('tpl');
            $tpl->addGlobal('form', new Form($session));
        }

        return parent::process($request, $handler);
    }
}
