<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:validation', aliases: ['mk:valid'])]
class Validation extends MakeAbstract
{
    protected string $blank = 'validation';

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new validation middleware.')
            ->setHelp('This command allows you to create a new validation middleware...')
            ->addArgument('name', InputArgument::REQUIRED, 'validation middleware name')
        ;
    }
}
