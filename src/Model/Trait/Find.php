<?php

namespace Sys\Model\Trait;

use Sys\Entity\Entity;
use PDO;

trait Find
{
    public function find($value, $column = 'id', $cache = true): ?Entity
    {
        if ($cache && ($entity = $this->cache($value, $column))) {
            return $entity;
        }

        $sql = "SELECT * FROM $this->table WHERE $column = ? LIMIT 1";
        $pdo = $this->qb->pdo();
        $sth = $pdo->prepare($sql);
        $sth->setFetchMode(PDO::FETCH_CLASS, $this->entityClass);
        $sth->execute([$value]);
        $entity = $sth->fetch();

        if (!$entity) {
            return null;
        }

        return ($cache) ? $this->cache($value, $column, $entity) : $entity;
    }

    public function cache($value, $column, $entity = null)
    {
        $key = md5((string) $value . $column);

        if (!$entity) {
            return $this->cache[$this->table][$key] ?? null;
        }

        $this->cache[$this->table][$key] = $entity;
        return $entity;
    }
}
