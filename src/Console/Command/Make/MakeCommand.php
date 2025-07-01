<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'make:command', aliases: ['mk:cmd'])]
class MakeCommand extends MakeAbstract
{
    protected string $folder = 'command';
    protected string $blank = 'command';

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new command.')
            ->setHelp('This command allows you to create a new command...')
            ->addArgument('name', InputArgument::REQUIRED, 'file name')
            ->addArgument('command_name', InputArgument::REQUIRED, 'command name')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->data['command'] = $input->getArgument('command_name');
        return parent::execute($input, $output);
    }
}
