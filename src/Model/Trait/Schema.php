<?php

namespace Sys\Model\Trait;

use PDO;

trait Schema
{
    private array $cache;
    
    public function tables(?string $prefix = null)
    {
        if (isset($this->cache['schema']['tables'])) {
            return $this->cache['schema']['tables'];
        }

        $suffix = (!empty($prefix)) ? " like '$prefix%'" : '';
        $sql = "SHOW TABLES" . $suffix;

        $sth = $this->qb->pdo()->query($sql);
        $tables = $sth->fetchAll(PDO::FETCH_COLUMN);

        $this->cache['schema']['tables'] = $tables;
        return $tables;
    }

    public function columns(?string $table = null): array
    {
        if (!$table) {
            $table = $this->table;
        }
        
        if (isset($this->cache['schema'][$table])) {
            return $this->cache['schema'][$table];
        }

        $sql = "SELECT `COLUMN_NAME`
        FROM `INFORMATION_SCHEMA`.`COLUMNS` 
        WHERE `TABLE_SCHEMA` = DATABASE()  
        AND `TABLE_NAME` = '$table'";

        $sth = $this->qb->pdo()->query($sql);
        $columns = $sth->fetchAll(PDO::FETCH_COLUMN);

        $this->cache['schema'][$table] = $columns;
        return $columns;
    }

    public function nextAI(?string $table = null): int
    {
        if (!$table) {
            $table = $this->table;
        }

        $sql = "SELECT AUTO_INCREMENT
        FROM information_schema.tables
        WHERE table_name = '$table'
        AND table_schema = DATABASE()";

        $sth = $this->qb->pdo()->query($sql);
        return $sth->fetchColumn();
    }

    public function prepareData(string $table, array $data)
    {
        return array_intersect_key($data, array_flip($this->columns($table)));
    }
}
