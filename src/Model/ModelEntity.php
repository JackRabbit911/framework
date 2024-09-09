<?php

namespace Sys\Model;

use Sys\Model\Trait\Find;
use Sys\Model\Trait\QueryBuilder;
use Sys\Model\Trait\Save;
use Sys\Model\Interface\Saveble;
use Sys\Observer\Trait\MagicCall;

abstract class ModelEntity implements Saveble
{
    use QueryBuilder;
    use Save;
    use Find;
    use MagicCall;

    protected string $table;
    protected string $entityClass;
}
