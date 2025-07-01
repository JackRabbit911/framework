<?php

namespace Sys\Console\Commands;

use Sys\Console\Command;

abstract class LogAbstract extends Command
{
    protected string $path = STORAGE . 'logs/';

    protected function configure()
    {
        $this->setHelp('Show or clear Log file. Arguments:')
            ->addArgument('file', 'Name of the logfile without extension', '')
            ->addArgument('lines', 'Number of last rows to be shown', 0);
    }

    public function execute($file, $lines)
    {
        if (empty($file)) {
            $this->all();
        } else {
            $file = $this->path . $file . '.log';

            if (!is_file($file)) {
                $this->climate->red()->inline('WARNING! ')
                    ->out("File <yellow>'$file'</yellow> doesn't exist");
            } else {
                $this->file($file, (int) $lines);
            }
        }

        exit;
    }

    protected function all() {}

    protected function file($file, $lines) {}

    protected function countLines($file)
    {
        $i = 0;
        $handle = fopen($file, 'r');

        while (!feof($handle)) {
            fgets($handle);
            $i++;
        }

        fclose($handle);
        return $i;
    }
}
