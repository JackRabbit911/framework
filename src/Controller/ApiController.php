<?php declare(strict_types=1);

namespace Sys\Controller;

use Sys\Middleware\CORSMiddleware;

abstract class ApiController extends BaseController
{
    protected array $headers;

    public function __construct(protected CORSMiddleware $cors){}

    protected function _before()
    {
        $contract = config('api_contracts', $this->request->getUri()->getPath());
        $this->headers = $this->cors->getHeaders($this->request, $contract);
    }

}
