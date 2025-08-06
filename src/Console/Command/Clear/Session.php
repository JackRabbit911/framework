<?php

declare(strict_types=1);

namespace Sys\Console\Command\Clear;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sys\Console\CallApi;
use Sys\Job\SessGC;

#[AsCommand(name: 'clear:sess')]
class Session extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Clear exired sessions')
            ->setHelp('This command clear exired sessions...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $lifetime = config('session', 'options.gc_maxlifetime');
        $result = (new CallApi(SessGC::class))->execute(['maxlifetime' => $lifetime]);
        $output->writeln((string) $result . ' files was deleted');

        return Command::SUCCESS;
    }
}
