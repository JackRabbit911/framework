<?php

declare(strict_types=1);

namespace Sys\Console\Command\Do;

use DateTime;
use DateTimeZone;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'do:down')]
class Down extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Turns on maintenance mode.')
            ->setHelp('This command turns on maintenance mode...')
            ->addArgument('retry_after', InputArgument::OPTIONAL, 'time to retry after in hours')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $hours_to_wait = $input->getArgument('retry_after') ?? 2;
        self::down($hours_to_wait);
        // $tz_name = date_default_timezone_get();
        // $local_tz = new DateTimeZone($tz_name);
        // $date = new DateTime('now', $local_tz);
        // $date->modify("+$hours_to_wait hours");
        // $date->setTimezone(new DateTimeZone('GMT'));
        // $retry_after_gmt = $date->format('D, d M Y H:i:s \G\M\T');

        // file_put_contents('./maintenance', $retry_after_gmt);
        $output->writeln('Maintenance mode is on');

        return Command::SUCCESS;
    }

    public static function down($hours_to_wait)
    {
        $tz_name = date_default_timezone_get();
        $local_tz = new DateTimeZone($tz_name);
        $date = new DateTime('now', $local_tz);
        $date->modify("+$hours_to_wait hours");
        $date->setTimezone(new DateTimeZone('GMT'));
        $retry_after_gmt = $date->format('D, d M Y H:i:s \G\M\T');

        file_put_contents('./maintenance', $retry_after_gmt);
    }
}
