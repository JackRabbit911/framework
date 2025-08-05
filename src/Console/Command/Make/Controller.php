<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:controller', aliases: ['mk:ctrl'])]
class Controller extends MakeAbstract
{
    protected string $blank = 'controller';

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new controller.')
            ->setHelp('This command allows you to create a new controller...')
            ->addArgument('name', InputArgument::REQUIRED, 'controller name')
        ;
    }
}
