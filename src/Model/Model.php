<?php

namespace Sys\Model;

use Sys\Model\Trait\QueryBuilder;

abstract class Model
{
    use QueryBuilder;

    protected string $table;
}
