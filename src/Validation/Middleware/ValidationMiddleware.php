<?php

namespace Az\Validation\Middleware;

use Az\Validation\Validation;
use HttpSoft\Response\JsonResponse;
use HttpSoft\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ValidationMiddleware implements MiddlewareInterface
{
    
    protected ?string $path = null;

    public function __construct(protected Validation $validation){}

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $lang = $request->getAttribute('i18n')?->lang() ?? 'en';
        $this->validation->setLang($lang);
        
        $this->setPath();
        $path = rawurldecode(rtrim($request->getUri()->getPath(), '/'));

        if ($this->path && $this->path !== $path) {
            return $handler->handle($request);
        }

        $data = ($request->getMethod() === 'GET') ? $request->getQueryParams()
            : $this->getData($request);
        
        $this->setRules($request);

        $data = $this->validate($request, $data);

        $request = $this->modifyRequest($request, $data);

        $this->debug($request, $data);

        $GLOBALS['request'] = $request;

        return ($data !== null) ? $handler->handle($request
                ->withParsedBody($data))
            : $this->errorHandler($request);
    }

    protected function setPath() {}

    protected function setRules(ServerRequestInterface $request) {}

    protected function modifyRequest(ServerRequestInterface $request, ?array $data): ServerRequestInterface
    {
        return $request;
    }

    protected function modifyData($data) {
        return $data;
    }

    protected function validate(ServerRequestInterface $request, array $data): ?array
    {
        $files = $request->getUploadedFiles();

        if ($this->validation->check($data, $files)) {
            return $this->modifyData($data);
        }

        return null;
    }

    protected function errorHandler(ServerRequestInterface $request): ResponseInterface
    {
        $session = $request->getAttribute('session');
        $session->flash('validation', $this->validation->getResponse());
        
        return new RedirectResponse($request->getServerParams()['HTTP_REFERER'], 302);
    }

    protected function debug(ServerRequestInterface $request, $data) {}

    private function getData($request)
    {
        $data = $request->getParsedBody();
        
        if (empty($data)) {
            $data = $request->getBody()->getContents();
        }

        if (is_string($data)) {
            $result = json_decode($data, true);

            if (json_last_error() > 0 ) {
                parse_str($data, $data);
            } else {
                return $result;
            }
        }

        return $data;
    }
}
