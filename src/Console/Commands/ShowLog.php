<?php

namespace Sys\Console\Commands;

final class ShowLog extends LogAbstract
{
    protected function all()
    {
        foreach (glob($this->path . '*.log') as $file) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $lines = $this->countLines($file);

            $data[] = ['File' => $filename, 'Lines' => $lines];
        }

        $this->climate->table($data);
    }

    protected function file($file, $lines)
    {
        $count = $this->countLines($file);
        $count = ($lines) ? $count - $lines : 0;

        $i = 0;
        $handle = fopen($file, 'r');
        $this->climate->br();

        while (!feof($handle)) {
            $buffer = fgets($handle);

            if ($i++ >= $count) {
                $this->climate->inline($i)->tab()->inline($buffer);
            }
        }

        fclose($handle);
        $this->climate->br()->out(realpath($file));
    }
}
