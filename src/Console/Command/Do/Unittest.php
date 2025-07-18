<?php

declare(strict_types=1);

namespace Sys\Console\Command\Do;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'do:test')]
class Unittest extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Execute Your tests.')
            ->setHelp('This command allows you execute tests...')
            ->addArgument('namespace', InputArgument::OPTIONAL, 'short alias of the namespace')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $namespace = $input->getArgument('namespace') ?? 'app';
        $test_path = config('test_paths', $namespace);

        passthru('../system/vendor/phpunit/phpunit/phpunit ' . $test_path);
        return Command::SUCCESS;
    }
}
