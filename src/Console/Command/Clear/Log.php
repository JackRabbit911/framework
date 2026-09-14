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
    private static string $dir = STORAGE . 'logs/';

    protected function configure(): void
    {
        $this
            ->setDescription('Clear log files')
            ->setHelp('This command clear log files...')
            ->addArgument(
                'names',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'filenames separated by spaces, without extensions',
                ['error.log']
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filenames = $input->getArgument('names');

        [$success, $error] = self::clear($filenames);

        if ($success) {
            $io->success($success);
        }

        if ($error) {
            $io->warning($error);
        }

        return Command::SUCCESS;
    }

    public static function clear(?array $names = null)
    {
        if (!$names) {
            $names = ['error.log'];
        }

        foreach ($names as $name) {
            $file = self::$dir . $name;
    
            if (!is_file($file)) {
                $not_found[] = $name;
            } else {
                file_put_contents($file, '');
                $found[] = $name;
            }
        }

        $success = $error = null;

        if (isset($found)) {
            $found_str = implode(', ', $found);
            $success = count($found) . ' files was successfully cleared (' . $found_str . ')';
        }

        if (isset($not_found)) {
            $not_found_str = implode(', ', $not_found);
            $error = count($not_found) . ' files is not found (' . $not_found_str . ')';
        }

        return [$success, $error];
    }
}
