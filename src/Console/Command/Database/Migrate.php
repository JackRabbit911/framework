<?php

declare(strict_types=1);

namespace Sys\Console\Command\Database;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sys\Console\Migrations\Migrate as Migrator;

#[AsCommand(name: 'db:migrate', aliases: ['db:mgrt'])]
class Migrate extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Execute migrattions up, down and show its.')
            ->setHelp('This command allows you execute migrattions up, down and show its...')
            ->addArgument('action', InputArgument::OPTIONAL, 'action name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migrator = container()->get(Migrator::class);

        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action') ?? 'show';

        if (!method_exists($migrator, $action)) {
            $io->error("Argument '$action' not recognized");
            return Command::SUCCESS;
        }

        [$title,$result] = $migrator->$action();

        $io->title($title);
        $io->text($result);
        $io->newLine();

        return Command::SUCCESS;
    }
}
