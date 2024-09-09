<?php

namespace Sys\Model\Trait;

use DI\Attribute\Inject;
use Pecee\Pixie\QueryBuilder\QueryBuilderHandler;

trait QueryBuilder
{
    #[Inject]
    protected QueryBuilderHandler $qb;
}
