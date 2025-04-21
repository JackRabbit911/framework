<?php declare(strict_types=1);

namespace Sys\Controller;

use Sys\Contract\UserInterface;
use Sys\Middleware\CORSMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class ApiController extends BaseController
{
    protected array $headers;
    protected ?UserInterface $user;

    public function __construct(protected CORSMiddleware $cors){}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $contract = config('api_contracts', $request->getUri()->getPath());
        $this->headers = $this->cors->getHeaders($request, $contract);
        $this->user = $request->getAttribute('user');

        return parent::handle($request);
    }

}
