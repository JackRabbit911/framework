<?php

declare(strict_types=1);

namespace Sys\Console\Command\Do;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'do:up')]
class Up extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Turns off maintenance mode.')
            ->setHelp('This command turns off maintenance mode...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        self::up();
        $output->writeln('Maintenance mode is off');

        return Command::SUCCESS;
    }

    public static function up()
    {
        $file = STORAGE . 'maintenance';

        if (is_file($file)) {
            unlink($file);
        }
    }
}
