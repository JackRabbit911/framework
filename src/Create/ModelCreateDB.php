<?php

namespace Sys\Create;

use PDO;

final class ModelCreateDB
{
    private PDO $pdo;

    public function __construct($host, $root_password)
    {
        $dsn = 'mysql:host=' . $host;
        $this->pdo = new PDO($dsn, 'root', $root_password);
    }

    public function create(string $dbname, string $password, ?string $username = null)
    {
        $username = (($username)) ?: $dbname;

        $sth = $this->pdo->query("SHOW DATABASES like '$dbname'");
        $dbs = $sth->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($dbs)) {
            $sql = "CREATE DATABASE `$dbname` COLLATE 'utf8mb4_general_ci';
            CREATE USER IF NOT EXISTS '$username'@'%' IDENTIFIED BY '$password';
            GRANT ALL PRIVILEGES ON $dbname.* TO '$username'@'%';
            FLUSH PRIVILEGES;";

            $this->pdo->exec($sql);
            return true;
        } else {
            return false;
        }
    }

    public function dbExists(string $dbname)
    {
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
        $sth = $this->pdo->query($sql);
        $dbs = $sth->fetchColumn();

        return !empty($dbs);
    }
}
