<?php

namespace Sys\Migrations\Commands;

use Sys\Migrations\UpDownTrait;

final class Show extends MigrationsCommandAbstract
{
    use UpDownTrait;

    protected string $dir;

    protected function configure()
    {
        $this->addArgument('path', 'migrations group', '');
    }

    public function execute($path)
    {
        $this->setUpDown($path);

        foreach ($this->up as $fn => $path) {
            $res[] = [
                'name' => '<green>' . $fn . '</green>',
                'path' => $path,
            ];
        }

        foreach ($this->down as $fn => $path) {
            $res[] = [
                'name' => '<yellow>' . $fn . '</yellow>',
                'path' => $path,
            ];
        }

        if (empty($res)) {
            $this->climate->out('No migrations found');
        } else {
            $this->climate->table($res);
        }       
    }
}
