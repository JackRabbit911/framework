<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Sys\Console\Migrations\CreateClass;

#[AsCommand(name: 'make:migration', aliases: ['mk:mgrt'])]
class Migration extends Command
{
    public function __construct(private CreateClass $creator)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new migrattion file.')
            ->setHelp('This command allows you to create a new migrattion file...')
            ->addArgument('table_name', InputArgument::REQUIRED, 'table name')
            ->addOption('alter', null, null, 'is alter table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getOption('alter') ? 'alter' : 'create';
        $table = $input->getArgument('table_name');

        $result = $this->creator->create($table, $action);

        $io = new SymfonyStyle($input, $output);

        if ($result === CreateClass::NO_BLANK) {
            $io->error("Blank not found");
        } elseif ($result === CreateClass::NO_DIR) {
            $io->error('Directory "' . CreateClass::DIR . '" is not writable');
        } else {
            $io->success("File $result was created succefully");
        }
        
        return Command::SUCCESS;
    }
}
