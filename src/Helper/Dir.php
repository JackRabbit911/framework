<?php

namespace Sys\Helper;

use FilesystemIterator;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use CallbackFilterIterator;
use SplFileInfo;

class Dir
{
    public function removeAll($dir)
    {
        $includes = new FilesystemIterator($dir);

        foreach ($includes as $include) {
            if(is_dir($include) && !is_link($include)) {
                $this->removeAll($include);
            } else {
                unlink($include);
            }
        }

        rmdir($dir);
    }

    public function clearDir($dir)
    {
        $iterator = new FilesystemIterator($dir);
       
        foreach ($iterator as $include) {
            if(is_dir($include) && !is_link($include)) {
                $this->clearDir($include);
                rmdir($include);
            } else {
                unlink($include);
            }           
        }
    }

    public function removeEmpty($dir)
    {
        if (!is_dir($dir)) {
            return 0;
        }

        $iterator = new RecursiveIteratorIterator (
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        $i = 0;
        foreach ($iterator as $file) {
            if ($file->isDir() && count(scandir($file->getPathname())) === 2) {
                rmdir($file->getPathname());
                $i++;
            }
        }
         
        return $i;
    }

    public function callbackIterator($dir, $callback)
    {
        $iterator = new RecursiveIteratorIterator (
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        return $callback($iterator);
    }

    public function clearByLifetime($dir, $lifetime)
    {
        $iterator = new RecursiveIteratorIterator (
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $i = 0;

        foreach ($iterator as $info) {
            if ($info->isDir()) {
                continue;
            }

            if ((time() - $info->getCTime()) > $lifetime) {
                unlink($info->getPathname());
                $i++;
            }
        }
         
        return $i;
    }

    public function clearByMask(string $dir, $mask)
    {
        $files = glob(trim($dir, '/') . '/' . $mask, GLOB_BRACE);
        $count = count($files);

        foreach ($files as $file) {
            unlink($file);
        }

        return $count;
    }

    public function getByMask(string $dir, $mask, $sort = false): array
    {
        $array = glob(trim($dir, '/') . '/' . $mask, GLOB_BRACE);

        if ($sort) {
            natsort($array);
        }

        return array_values($array);
    }
}
