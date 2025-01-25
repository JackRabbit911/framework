<?php

namespace Sys\Model\Trait;

use DI\Attribute\Inject;
use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;

trait QueryBuilder
{
    #[Inject]
    protected IQueryBuilderHandler $qb;
}
