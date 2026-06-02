<?php

declare(strict_types=1);

namespace Sys\Pagination;

use Psr\Http\Message\ServerRequestInterface;
use Sys\Template\Component;
use Sys\Helper\Uri;

class Pagination57 extends Component
{
    use Utils;

    protected array $perPages;
    protected ?string $perPage;
    private Uri $helper;
    private int $limit;
    private int $page;

    public function __construct(ServerRequestInterface $request, int $total, ?int $limit = null)
    {
        $path = $request->getUri()->getPath();
        $query = $request->getQueryParams();
        $page = $query['page'] ?? 1;
        $this->page = (int) $page;
        $limit = $query['limit'] ?? $limit ?? $this->perPages[0] ?? 24;
        $this->limit = (int) $limit;
        $count_pages = (int) ceil($total / $this->limit);

        $this->helper = new Uri($request);
        $this->data = $this->getPaginationData($count_pages, $this->page);
    }

    public static function offset($page, $limit)
    {
        return ((int) $page - 1) * $limit;
    }
}
