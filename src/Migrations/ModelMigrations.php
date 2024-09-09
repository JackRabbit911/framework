<?php

namespace Sys\Migrations;

use PDO;
use Sys\Model\Trait\QueryBuilder;
use Sys\Model\Trait\Schema;

final class ModelMigrations
{
    use QueryBuilder;
    use Schema;

    protected string $table = 'migrations';

    public function get($path)
    {
        $tables = $this->tables('migrations');
        
        if (empty($tables)) {
            return [];
        }

        $select = $this->qb->table($this->table)
            ->select(['name', 'path']);
        
        if ($path !== null) {
            $select->where('path', '=', $path);
        }

        return $select
            ->setFetchMode(PDO::FETCH_KEY_PAIR)
            ->get();
    }

    public function do($sql, $insert, $delete)
    {
        $sth = $this->qb->pdo()->query($sql);
        $sth->closeCursor();

        $table = $this->qb->table($this->table);

        if (!empty($insert)) {
            $table->insert($insert);
        }

        if (!empty($delete)) {
            foreach ($delete as $row) {
                $table
                    ->where('name', '=', $row['name'])
                    ->where('path', '=', $row['path'])
                    ->delete();
            }
        }

        return true;
    }
}
