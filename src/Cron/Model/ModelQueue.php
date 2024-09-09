<?php

namespace Sys\Cron\Model;

use PDO;
use Sys\Model\Trait\QueryBuilder;
use Attribute;
use Sys\Model\Interface\Saveble;
use Sys\Model\Trait\Save;
use Sys\Cron\Entity\Queue;

class ModelQueue implements Saveble
{
    use QueryBuilder;
    use Save {
        save as baseSave;
    }

    protected string $table = 'queues';

    public function save(Queue $queue): void
    {
        if (!isset($queue->id)) {
            do {
                $queue->id = uniqid();
            } while ($this->qb->table($this->table)->find($queue->id));
        }

        $this->baseSave($queue);
    }

    public function insert($data)
    {
        $table = $this->qb->table($this->table);

        do {
            $id = uniqid();
        } while ($table->find($id));

        if (method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }
       
        $data['id'] = $id;
        $table->insert($data);

        return $id;
    }

    public function first()
    {
        return $this->qb->table($this->table)->first();
    }

    public function getNames()
    {
        $lastTime = $this->qb->table($this->table)
            ->select($this->qb->raw('MAX(created)'))
            ->where('status', '=', Queue::READY)
            ->where('created', '<=', $this->qb->raw('CURRENT_TIMESTAMP'))
            ->setFetchMode(PDO::FETCH_COLUMN)
            ->first();

        $names = $this->qb->table($this->table)
            ->select('name')
            ->where('status', '=', Queue::READY)
            ->where('created', '<=', $lastTime)
            ->groupBy('name')
            ->setFetchMode(PDO::FETCH_COLUMN)
            ->get();

        $this->qb->table($this->table)
            ->where('status', '=', Queue::READY)
            ->where('created', '<=', $lastTime)
            ->update(['status' => Queue::IN_PROCCESS]);

        return [$names, $lastTime];
    }

    public function get($name, $lastTime)
    {
        $queue = $this->qb->table($this->table)
            ->select(['id', 'job', 'data'])
            ->where('name', '=', $name)
            ->where('status', '=', Queue::IN_PROCCESS)
            ->where('created', '<=', $lastTime)
            ->orderBy('created')
            ->get();

        $this->qb->table($this->table)
            ->where('status', '=', Queue::IN_PROCCESS)
            ->where('created', '<=', $lastTime)
            ->update(['status' => Queue::SUCCESS]);

        return $queue;
    }

    public function updateStatus($ids, $status)
    {
        $table = $this->qb->table($this->table);

        if (!empty($ids)) {
            $table->whereIn('id', $ids);
            $table->update(['status' => $status]);
        }   
    }

    public function update($status, $newStatus)
    {
       $this->qb->table($this->table)
        ->where('status', '=', $status)
        ->update(['status' => $newStatus])
       ;
    }

    public function deleteByStatus($compare, $status)
    {
        return $this->qb->table($this->table)
            ->where('status', $compare, $status)
            ->delete();
    }
}
