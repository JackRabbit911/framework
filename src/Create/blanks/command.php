<?=$php?>

namespace <?=$namespace?>;

use Sys\Console\Command;

class <?=$classname?> extends Command
{
    protected function configure()
    {
        $this->setHelp()
            ->addArgument()
            ->addOption();
    }


    public function execute()
    {

    }
}
