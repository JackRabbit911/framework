<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: 'make:model')]
class Model extends MakeAbstract
{
    protected string $folder = 'model';
    protected string $blank = 'model';

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new model.')
            ->setHelp('This command allows you to create a new model...')
            ->addArgument('name', InputArgument::REQUIRED, 'model name')
        ;
    }
}
