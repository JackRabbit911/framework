<?php

declare(strict_types=1);

namespace Az\Validation\Middleware;

use Az\Validation\Validation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

abstract class ApiValidationMiddleware implements MiddlewareInterface, IValidationMiddleware
{
    protected Validation $validation;
    protected array $data;

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface
    {
        $this->validation = container()->get(Validation::class);
        $lang = $this->detectLang($request);
        $this->validation->setLang($lang);
        $this->data = $this->getData($request);

        $this->setRules($request);

        $files = $request->getUploadedFiles();        
        $check = $this->validation->check($this->data, $files, true);

        return $this->getResponse($request, $handler, $check);
    }

    protected function setRules(ServerRequestInterface $request) {}

    protected function modifyData($data)
    {
        return $data;
    }

    protected function getData($request)
    {
        $data = $request->getBody()->getContents();

        if (empty($data)) {
            $data = $request->getParsedBody();
        }

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        $data += $request->getQueryParams();

        return $data;
    }

    private function detectLang(ServerRequestInterface $request, string $default = 'en')
    {
        $header = $request->getHeaderLine('Accept-Language');

        if (empty($header)) {
            return $default;
        }

        $pattern = '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i';

        preg_match_all($pattern, $header, $matches);
        $langs = [];

        if (count($matches[1])) {
            $langs = array_combine($matches[1], $matches[4]);

            foreach ($langs as $lang => $q) {
                if ($q === '') {
                    $langs[$lang] = 1.0;
                } else {
                    $langs[$lang] = (float) $q;
                }
            }

            arsort($langs, SORT_NUMERIC);
        }

        return substr(array_keys($langs)[0], 0, 2);
    }
}
