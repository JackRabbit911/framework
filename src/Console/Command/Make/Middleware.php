<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:middleware', aliases: ['mk:mw'])]
class Middleware extends MakeAbstract
{
    protected string $blank = 'middleware';

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new middleware.')
            ->setHelp('This command allows you to create a new middleware...')
            ->addArgument('name', InputArgument::REQUIRED, 'middleware name')
        ;
    }
}
