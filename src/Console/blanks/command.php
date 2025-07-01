<?=$php?>


declare(strict_types=1);

namespace <?=$namespace?>;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

#[AsCommand(name: '<?=$command?>')]
class <?=$classname?> extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Description')
            ->setHelp('Help string')
            ->addArgument('arg', InputArgument::REQUIRED, 'this is arg')
            ->addOption('yell', null, InputOption::VALUE_OPTIONAL, 'Should I yell while greeting?')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Some response');

        return Command::SUCCESS;
    }
}
