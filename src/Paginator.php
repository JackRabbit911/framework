<?php declare(strict_types=1);

namespace Sys;

use Psr\Http\Message\ServerRequestInterface;
use Sys\Template\Component;

class Paginator extends Component
{
    private ServerRequestInterface $request;
    private int $countRows;
    private int $countPages;
    private array $queryParams;
    private string $path;
    private int $currentPage;
    private string $key;
    private int $limit;
    private string $view;

    public function __construct(ServerRequestInterface $request, int $countRows, int $limit, string $view = 'pagination', string $key = 'page')
    {
        $this->request = $request;
        $this->countRows = $countRows;
        $this->countPages = (int) ceil($countRows/$limit);
        $this->queryParams = $this->request->getQueryParams();
        $this->path = rtrim($this->request->getUri()->getPath(), '/');
        $currentPage = $this->queryParams[$key] ?? 1;
        $this->currentPage = (int) $currentPage;
        $this->key = $key;
        $this->limit = $limit;
        $this->view = $view;
    }

    public function render()
    {
        return view($this->view, ['p' => $this]);
    }

    public function offset()
    {
        static $offset;

        if ($offset !== null) {
            return $offset;
        }

        $offset = ((int) $this->currentPage - 1) * $this->limit;

        if ($offset < 0) {
            throw new \Exception("Offset cannot take a negative value ($offset)");
        }

        return $offset;
    }

    public function isFirst()
    {
        return $this->currentPage === 1;
    }

    public function isLast()
    {
        return $this->currentPage === $this->countPages;
    }

    public function countRows()
    {
        return $this->countRows;
    }

    public function limit()
    {
        return $this->limit;
    }

    public function first()
    {
        return $this->link(1);
    }

    public function prev()
    {
        return $this->link($this->currentPage - 1);
    }

    public function num($num)
    {
        return $this->path . '?' . $this->key . '=' . $num;
    }

    public function next()
    {
        $next = ($this->currentPage < $this->countPages) ? $this->currentPage + 1 : false;
        return $this->link($next);
    }

    public function last()
    {
        $last = ($this->currentPage < $this->countPages) ? $this->countPages : false;
        return $this->link($last);
    }

    public function currentPage()
    {
        return $this->currentPage;
    }

    private function link($page)
    {
        if ($page > 1) {
            $this->queryParams[$this->key] = $page;
        } else {
            unset($this->queryParams[$this->key]);
        }

        return (!empty($this->queryParams)) 
            ? $this->path . '?' . http_build_query($this->queryParams) : $this->path;
    }
}
