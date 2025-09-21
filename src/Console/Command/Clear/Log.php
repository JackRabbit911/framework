<?php

declare(strict_types=1);

namespace Sys\Console\Command\Clear;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'clear:log')]
class Log extends Command
{
    private string $dir = 'storage/logs/';

    protected function configure(): void
    {
        $this
            ->setDescription('Clear log files')
            ->setHelp('This command clear log files...')
            ->addArgument('name', InputArgument::OPTIONAL, 'log file name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $file = $input->getArgument('name') ?? 'error.log';
        $file = $this->dir . $file;

        if (!is_file($file)) {
            $io->error("$file is not found");
        } else {
            file_put_contents($file, '');
            $io->success("The file $file was successfully cleared.");
        }

        return Command::SUCCESS;
    }
}
