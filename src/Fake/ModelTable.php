<?php declare(strict_types=1);

namespace Sys\Fake;

use Sys\Fake\Insertable;
use Sys\Model\Trait\QueryBuilder;

class ModelTable implements Insertable
{
    use QueryBuilder;

    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function insert($data): int
    {
        $insertIds = $this->qb->table($this->table)->insert($data);       
        return count($insertIds);
    }
}
