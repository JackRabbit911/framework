<?php

declare(strict_types=1);

namespace Sys\Helper;

use Psr\Http\Message\ServerRequestInterface;

class Uri
{
    private string $path;
    private array $queryParams;

    public function __construct(private ServerRequestInterface $request)
    {
        $this->path = $request->getUri()->getPath();
        $this->queryParams = $request->getQueryParams();

    }

    public function replaceQueryParams(array $params): string
    {
        $new_query_params = array_replace($this->queryParams, $params);
        $query_str = http_build_query($new_query_params);

        return (!empty($query_str)) ? $this->path . '?' . $query_str : $$this->path;
    }
}
