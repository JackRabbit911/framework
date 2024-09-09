<?php

namespace Sys\Console\Commands;

final class ClearLog extends LogAbstract
{
    protected function all()
    {
        foreach (glob($this->path . '*.log') as $file) {
            $filename = pathinfo($file, PATHINFO_BASENAME);
            $data[] = $filename;

            unlink($file);
        }

        $this->climate->out('Files:');
        $this->climate->yellow($data);
        $this->climate->out('was removed');
    }

    protected function file($file, $lines)
    {
        $count = $this->countLines($file);
        $count = ($lines) ? $count - $lines : $count + 1;

        $i = 0;
        $data = '';
        $handle = fopen($file, 'r');
        $this->climate->br();

        while (!feof($handle)) {
            $buffer = fgets($handle);

            if ($i++ >= $count) {
                $data .= $buffer;
            }
        }

        fclose($handle);
        file_put_contents($file, $data);

        if (!empty($data)) {
            $this->climate->out($data);
        }

        $this->climate->out($count . ' lines was removed');
    }
}
