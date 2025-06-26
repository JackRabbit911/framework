<?php

declare(strict_types=1);

namespace Sys\Model;

use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;

abstract class MysqlModel
{
    public function __construct(protected IQueryBuilderHandler $qb){}
}
