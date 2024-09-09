<?php

namespace Sys\Model\Trait;

use Sys\Entity\Entity;

trait Save
{
    use Schema;

    public function save(Entity|array $data): mixed
    { 
        if (!is_array($data)) {
            if (method_exists($data, 'prepareProps')) {
                $data->prepareProps();
            }

            if (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = (array) $data;
            }
        }

        $data = array_intersect_key($data, array_flip($this->columns($this->table)));

        return $this->qb->table($this->table)
            ->onDuplicateKeyUpdate($data)
            ->insert($data);
    }
}
