<?php

namespace Sys\Migrations;

use Sys\Console\CallApi;
use Sys\Migrations\ModelMigrations;

trait UpDownTrait
{
    private array $up = [];
    private array $down = [];

    private function setUpDown($path)
    {
        $this->down = (new CallApi(ModelMigrations::class, 'get'))->execute(['path' => $path]);
        
        if ($path === '') {
            $iterator = new \IteratorIterator(
                new \FilesystemIterator(
                    $this->dir,
                    \FilesystemIterator::SKIP_DOTS
                ),
            );
        } else {
            $iterator = new \RecursiveIteratorIterator (
                new \RecursiveDirectoryIterator(
                    $this->dir . $path,
                    \FilesystemIterator::SKIP_DOTS
                ),
            );
        }

        foreach ($iterator as $file) {
            if ($file->isDir()) continue;
            
            $name = $file->getFilename();

            if (array_key_exists($name, $this->down)) continue;

            $search = [trim($this->dir, '/'), $name];
            $folder = trim(str_replace($search, '', $file->getPath()), '/');

            $this->up[$name] = $folder;
        }

        krsort($this->up);
        krsort($this->down);
    }
}
