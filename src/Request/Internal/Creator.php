<?php

declare(strict_types=1);

namespace Sys\Request\Internal;

use HttpSoft\Message\ServerRequest;
use HttpSoft\ServerRequest\PhpInputStream;
use HttpSoft\ServerRequest\SapiNormalizer;
use HttpSoft\ServerRequest\ServerNormalizerInterface;
use HttpSoft\ServerRequest\UploadedFileCreator;

class Creator
{
    public function __construct(private ?ServerNormalizerInterface $normalizer = null)
    {
        $this->normalizer ??= new SapiNormalizer();
    }

    public function create($method, $url, $params = [])
    {
        $server = $_SERVER;       
        [$uri, $queryStr] = $this->normalizeUrl($url);       
        $server['REQUEST_URI'] = $uri;
        $get = [];

        if ($queryStr) {
            $server['QUERY_STRING'] = $queryStr;
            parse_str($queryStr, $get);
        }

        $server['REQUEST_METHOD'] = $method;
        $server = $this->normalizeHeaders($server, $params);

        return new ServerRequest(
            $server,
            UploadedFileCreator::createFromGlobals($params['files'] ?? $_FILES),
            $params['cookie'] ?? $_COOKIE,
            array_merge($_GET, $params['get'] ?? [], $get),
            $params['post'] ?? $_POST,
            $this->normalizer->normalizeMethod($server),
            $this->normalizer->normalizeUri($server),
            $this->normalizer->normalizeHeaders($server),
            new PhpInputStream(),
            $this->normalizer->normalizeProtocolVersion($server)
        );
    }

    private function normalizeUrl($url)
    {
        $arr = explode('?', $url);
        $uri = $arr[0];
        $queryStr = $arr[1] ?? null;

        return [$uri, $queryStr];
    }

    private function normalizeHeaders($server, $params)
    {
        if (isset($params['headers'])) {
            foreach ($params['headers'] as $name => $value) {
                $server['HTTP_' . strtoupper($name)] = $value;
            }
        }
        
        return $server;
    }
}
