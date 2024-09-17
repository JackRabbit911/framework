<?php

namespace Sys\Create\Commands;

use Sys\Console\Command;
use Sys\Console\CallApi;
use Sys\Create\ModelCreateDB;

final class CreateDB extends Command
{
    protected function configure()
    {
        $this->addArgument('dbname', 'Database name', '')
            ->addArgument('password', 'Password for database', '')
            ->addArgument('username', 'Username for database', '');
    }

    public function execute($dbname, $password, $username)
    {
        $connect = config('database', 'connect.mysql');

        $args = [
            'host' => $connect['host'],
            'root_password' => $connect['root_password'],
        ];

        $data = [
            'dbname' => (empty($dbname)) ? $connect['database'] : $dbname,
            'password' => (empty($password)) ? $connect['password'] : $password,
            'username' => (empty($username)) ? $connect['username'] : $username,
        ];

        $call = new CallApi(ModelCreateDB::class, 'create', $args);
        $res = $call->execute($data);

        if ($res) {
            $this->climate->lightGreen("Database {$data['dbname']} was created successful!");
        } else {
            $this->climate->red()->inline('Warning! ');
            $this->climate->out("Database '{$data['dbname']}' is allready esists");
        }
    }
}
