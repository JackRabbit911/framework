<?php declare(strict_types=1);

namespace Sys\Controller;

use HttpSoft\Response\JsonResponse;
use Sys\Contract\UserInterface;
use Sys\Middleware\CORSMiddleware;
use Az\Route\Route;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ApiController implements RequestHandlerInterface// extends BaseController
{
    use InvokeTrait;

    protected ServerRequestInterface $request;
    protected array $parameters;
    protected array $headers;
    protected ?UserInterface $user;

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;
        $route = $request->getAttribute(Route::class);
        $this->parameters = $route->getParameters();
        $this->user = $request->getAttribute('user');

        [$handler, $action] = $route->getHandler();

        $this->_before();
        $response = $this->call($action, $this->parameters);

        if (!$response instanceof ResponseInterface) {
            $cors = container()->get(CORSMiddleware::class);
            $contract = config('api_contracts', $request->getUri()->getPath());
            $headers = $cors->getHeaders($request, $contract);
            $response = new JsonResponse($response, 200, $headers);
        }

        $this->_after($response);
        return $response;
    }

    protected function _before() {}

    protected function _after(&$response) {}
}
