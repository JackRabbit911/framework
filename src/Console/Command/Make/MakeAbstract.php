<?php

declare(strict_types=1);

namespace Sys\Console\Command\Make;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class MakeAbstract extends Command
{
    protected string $folder = '';
    protected string $blank = '';
    protected array $data = [];

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name');
        $filepath = $this->getFilepath($name);

        if (is_file($filepath)) {
            $io->warning("File $filepath already exists");
            return Command::SUCCESS;
        }

        $info = pathinfo($filepath);
        $dir = $info['dirname'];

        $data = [
            'php' => '<?php',
            'namespace' => $this->getNamespace($dir),
            'classname' => $info['filename'],
        ];

        $data = array_merge($this->data, $data);

        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        
        if (!is_writable($dir)) {
            chmod($dir, 0775);
        }
        
        if (!is_dir($dir) || !is_writable($dir)) {
            $io->error("$dir is not writable");
            return Command::SUCCESS;
        }
        
        $content = $this->createContent($data);

        if (!$content) {
            $io->error("Blank {$this->blank} not found");
            return Command::SUCCESS;
        }

        file_put_contents($filepath, $content);
        
        $io->success("File $filepath was created succefully");
        return Command::SUCCESS;
    }

    protected function getFilepath($name)
    {
        if (str_starts_with($name, '/') || str_starts_with($name, '\\')) {
            $name = ltrim($name, '\\/');
        } else {
            $name = ucfirst($this->folder) . '/' . $name;
        }

        $filename = str_replace(['\\', '.php'], ['/', ''], $name) . '.php';
        return APPPATH . $filename;
    }

    protected function getNamespace($dirname)
    {
        return str_replace([APPPATH, '/'], ['App\\', '\\'], $dirname);
    }

    protected function createContent($data)
    {
        $blank = FRAMEWORK . 'Console/blanks/' . $this->blank . '.php';

        if (!is_file($blank)) {
            return false;
        }

        return render($blank, $data);
    }
}
