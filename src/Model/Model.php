<?php

namespace Sys\Model;

use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;

abstract class Model
{
    protected IQueryBuilderHandler $qb;
    protected string $table;

    public function __construct()
    {
        $this->qb = container()->get(IQueryBuilderHandler::class);
    }
}
