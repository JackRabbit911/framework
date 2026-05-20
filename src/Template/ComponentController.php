<?php

declare(strict_types=1);

namespace Sys\Template;

use Az\Route\Route;
use HttpSoft\Response\EmptyResponse;
use HttpSoft\Response\HtmlResponse;
use HttpSoft\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Sys\Contract\UserInterface;
use Sys\Controller\InvokeTrait;

abstract class ComponentController extends Component implements RequestHandlerInterface, CmpInterface
{
    use InvokeTrait;

    protected ServerRequestInterface $request;
    protected ?UserInterface $user;
    protected ?string $view = null;
    protected array $data = [];
    protected ?string $js = null;
    private TemplateInterface $tpl;
    private App $app;

    public function __construct()
    {
        $this->tpl = container()->get(TemplateInterface::class);
        $this->app = container()->get(App::class);
        $this->tpl->addGlobal('app', $this->app);
        $this->js($this->js);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $this->user = $request->getAttribute('user');
        $this->app->add('user', $this->user);
        
        $route = $request->getAttribute(Route::class);
        [$handler, $action] = $route->getHandler();

        return $this->call($action, $route->getParameters());
    }

    public function js(?string $script = null)
    {
        if ($script) {
            $this->app->js($script);
        }

        return $this;
    }

    protected function html(): ResponseInterface
    {
        return new HtmlResponse($this->render());
    }

    protected function json(): ResponseInterface
    {
        return new JsonResponse($this->data);
    }

    protected function empty(): ResponseInterface
    {
        return new EmptyResponse();
    }
}
