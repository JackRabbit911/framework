<?php

declare(strict_types=1);

namespace Sys\CSRF\Driver;

use Override;
use Sys\Model\MysqlModel;
use Pecee\Pixie\Exceptions\DuplicateEntryException;
use PDO;
use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;

class Db extends MysqlModel implements DriverInterface
{
    public const CREATE_TABLE_CSRF = "CREATE TABLE `csrf` (
    `token` char(64) COLLATE latin1_bin NOT NULL,
    `user_id` int(11) unsigned DEFAULT NULL,
    `user_agent` char(32) COLLATE latin1_bin DEFAULT NULL,
    `expire` datetime NOT NULL,
    UNIQUE KEY `token` (`token`),
    KEY `user_id` (`user_id`),
    KEY `expire` (`expire`),
    KEY `user_agent` (`user_agent`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin";

    private string $user_agent;

    public function __construct(IQueryBuilderHandler $qb)
    {
        parent::__construct($qb);

        $this->user_agent = md5($_SERVER['HTTP_USER_AGENT']);
    }

    public function validate(string $token, ?int $user_id): bool
    {
        $now = $this->qb->raw('NOW()');

        $table = $this->qb->table('csrf')
            ->where('token', '=', hash('sha256', $token))
            ->where('expire', '>=', $now);

        if ($user_id) {
            $table->where('user_id', '=', $user_id);
        } else {
            $table->whereNull('user_id');
        }

        return $table->count() > 0 ?: false;
    }

    public function generate(?int $user_id, int $lifetime): string
    {
        $salt = $_SERVER['HTTP_USER_AGENT'] ?? uniqid();

        while (true) {
            $token = hash_hmac('sha256', bin2hex(random_bytes(16)), $salt);

            $expire_string = $this->qb->query("SELECT NOW() + INTERVAL ? SECOND", [$lifetime])
                ->setFetchMode(PDO::FETCH_COLUMN)
                ->first();

            try {
                $this->qb->table('csrf')
                    ->insert([
                        'token' => hash('sha256', $token),
                        'user_id' => $user_id,
                        'user_agent' => $this->user_agent,
                        'expire' => $expire_string,
                    ]);

                break;
            } catch (DuplicateEntryException $e) {
                continue;
            }
        }

        return $token;
    }

    public function gc(): int
    {
        $now = $this->qb->raw('NOW()');

        $stmt = $this->qb->table('csrf')
            ->where('expire', '<', $now)
            ->delete();

        return $stmt->rowCount();
    }

    public function delete(string $token): void
    {
        $this->qb->table('csrf')
            ->where('user_agent', '=', $this->user_agent)
            ->where('token', '=', hash('sha256', $token))
            ->delete();
    }

    public function deleteByUser(int $user_id)
    {
        $this->qb->table('csrf')
            ->where('user_id', '=', $user_id)
            ->delete();
    }

    public function deleteByUserAgent(int $user_id)
    {
        $this->qb->table('csrf')
            ->where('user_agent', '=', $this->user_agent)
            ->where('user_id', '=', $user_id)
            ->delete();
    }

    public function deleteOthers(int $user_id)
    {
        $this->qb->table('csrf')
            ->where('user_id', '=', $user_id)
            ->where('user_agent', '!=', $this->user_agent)
            ->delete();
    }
}
