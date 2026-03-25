<?php

declare(strict_types=1);

namespace Sys\CSRF\Driver;

use Sys\Model\MysqlModel;
use Pecee\Pixie\Exceptions\DuplicateEntryException;
use PDO;

class Db extends MysqlModel implements DriverInterface
{
    public const CREATE_TABLE_CSRF = "CREATE TABLE `csrf` (
  `token` varchar(32) COLLATE latin1_bin NOT NULL,
  `user_id` int(11) unsigned DEFAULT NULL,
  `expire` datetime NOT NULL,
  UNIQUE KEY `token_user_id_expire` (`token`,`user_id`,`expire`),
  KEY `token` (`token`),
  KEY `user_id` (`user_id`),
  KEY `expire` (`expire`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin
";

    public function validate(string $token, ?int $user_id): bool
    {
        $now = $this->qb->raw('NOW()');

        $table = $this->qb->table('csrf')
            ->where('token', '=', $token)
            ->where('expire', '>=', $now);

        if ($user_id) {
            $table->where('user_id', '=', $user_id);
        } else {
            $table->whereNull('user_id');
        }

        $stmt = $table->delete();

        return $stmt->rowCount() > 0;
    }

    public function generate(?int $user_id, string $form, int $expire): string
    {
        $this->delete($user_id, $form);

        while (true) {
            $salt = $_SERVER['HTTP_USER_AGENT'] ?? uniqid();
            $token = md5($salt . time() . bin2hex(random_bytes(12)));

            $expire_string = $this->qb->query("SELECT NOW() + INTERVAL ? SECOND", [$expire])
                ->setFetchMode(PDO::FETCH_COLUMN)
                ->first();

            try {
                $this->qb->table('csrf')
                    ->insert([
                        'token' => $token,
                        'user_id' => $user_id,
                        'form' => $form,
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

    private function delete(?int $user_id, string $form)
    {
        $this->qb->table('csrf')
            ->where('user_id', '=', $user_id)
            ->where('form', '=', $form)
            ->delete();
    }
}
