<?php

declare(strict_types=1);

namespace Sys\Console\Migrations;

use Sys\Model\MysqlModel;
use PDO;

class Model extends MysqlModel
{
    private string $table = 'migrations';

    public function exec(string $sql): void
    {
        $this->qb->pdo()->exec($sql);
    }

    public function insert(string|array $data): void
    {
        if (is_string($data)) {
            $data = ['name' => $data];
        }

        $this->qb->table($this->table)
            ->insert($data);
    }

    public function get(): array
    {
        if (!$this->isTable()) {
            return [];
        }

        return $this->qb->table($this->table)
            ->select('name')
            ->setFetchMode(PDO::FETCH_COLUMN)
            ->get();
    }

    public function getLast(): string|false
    {
        if (!$this->isTable()) {
            return false;
        }

        return $this->qb->table($this->table)
            ->select('name')
            ->setFetchMode(PDO::FETCH_COLUMN)
            ->orderBy('name', 'desc')
            ->first();
    }

    public function delete(string $name): void
    {
        $this->qb->table($this->table)
            ->where('name', '=', $name)
            ->delete();
    }

    private function isTable(): bool
    {
        $sql = "SHOW TABLES LIKE '{$this->table}';";
        return $this->qb->query($sql)->first() ? true : false; 
    }
}
