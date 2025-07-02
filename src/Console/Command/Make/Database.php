<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sys\Console\CallApi;
use Sys\Model\ModelCreateDB;

#[AsCommand(name: 'make:database', aliases: ['mk:db'])]
class Database extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new database.')
            ->setHelp('This command allows you to create a new database...')
            ->addArgument('connection', InputArgument::OPTIONAL, 'db connection')
            ->addArgument('db_name', InputArgument::OPTIONAL, 'db name')
            ->addArgument('db_password', InputArgument::OPTIONAL, 'db password')
            ->addArgument('db_username', InputArgument::OPTIONAL, 'db username')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $connection = $input->getArgument('connection') ?? env('DB_CONNECTION');
        $config = config('database', $connection);

        $args = [
            'connection' => $connection,
            'host' => $config['host'],
            'root_password' => env('DB_ROOT_PASSWORD'),
        ];

        $db_name = $input->getArgument('db_name') ?? $config['database'];

        $data = [
            'dbname' => $db_name,
            'password' => $input->getArgument('db_password') ?? $config['password'],
            'username' => $input->getArgument('db_username') ?? $config['username'],
        ];

        $response = (new CallApi(ModelCreateDB::class, 'create', $args))->execute($data);

        ($response)
            ? $io->success("Database $db_name was created successfully")
            : $io->warning("Database $db_name already exists");

        return Command::SUCCESS;
    }
}
