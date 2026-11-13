<?php

declare(strict_types=1);

namespace Sys\Worker;

use Sys\Model\Model;
use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;
use PDO;

class QueueDB extends Model implements QueueInterface
{
    protected string $table = 'queue_jobs';
    private PDO $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->qb->pdo();
    }

    public function push(array $jobs): void
    {
        

        $data = array_map(function ($v) {
            return [
                'id' => bin2hex(random_bytes(8)),
                'payload' => $v,
            ];
        }, $jobs);

        $this->qb->table($this->table)
            ->insert($data);
    }

    public function pop(): array
    {
        $wid = bin2hex(random_bytes(8));

        $update = "UPDATE `{$this->table}`
        SET `status` = 'processing', `worker_id` = :worker_id
        WHERE `status` = 'pending' AND `attempts` < 3";

        $select = "SELECT * FROM {$this->table} 
        WHERE worker_id = :worker_id";

        $stmt = $this->pdo->prepare($update);
        $stmt->execute([':worker_id' => $wid]);

        if ($stmt->rowCount() === 0) {
            return [];
        }

        $stmtSelect = $this->pdo->prepare($select);
        $stmtSelect->execute([':worker_id' => $wid]);
        
        return $stmtSelect->fetchAll(PDO::FETCH_OBJ);
    }

    public function delete(string $id): void
    {
        $this->qb->table($this->table)
            ->where('id', '=', $id)
            ->delete();
    }

    public function getAttempts(string $id): int
    {
        return $this->qb->table($this->table)
            ->select('attempts')
            ->setFetchMode(PDO::FETCH_COLUMN)
            ->find($id);
    }

    public function setStatus(string $id, string $status, ?int $attempts = null): void
    {
        $data['status'] = $status;

        if ($attempts) {
            $data['attempts'] = $attempts;
        }

        $this->qb->table($this->table)
            ->where('id', '=', $id)
            ->update($data);
    }

    public function getFailed(): array
    {
        return $this->qb->table($this->table)
            ->where('status', '=', 'failed')
            ->get();
    }

    public function clear(): int
    {
        $stmt = $this->qb->table($this->table)
            ->where('status', '=', 'failed')
            ->delete();

        return $stmt->rowCount();
    }
}
