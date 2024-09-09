<?php

namespace Sys\Migrations;

final class File
{
    private string $dir;

    public function __construct()
    {
        $this->dir = config('common', 'migrations_dir') ?? CONFIG . 'migrations/';

        if (!is_dir($this->dir)) {
            mkdir($this->dir, 0777, true);
        }
    }

    public function createFile($action, $tableName, $path = '')
    {
        $dateFormat = date('Y-m-d_H-i-s');
        $filename = $dateFormat . '_' . $action . '-table-' . $tableName . '.php';
        $classname = $this->getClassName($filename);
        $content = file_get_contents(__DIR__ . '/Blank.php');
        $content = str_replace('classname', $classname, $content);

        if (!empty($path)) {
            $this->dir .= $path . '/';
            if (!is_dir($this->dir)) {
                mkdir($this->dir, 0777, true);
            }
        }

        $result = file_put_contents($this->dir . $filename, $content);
        chmod($this->dir . $filename, 0777);

        return ($result) ? $filename : false;
    }

    public function list($path = '', $basename = true)
    {
        if (!empty($path)) {
            $this->dir .= $path . '/';
        }

        $files = glob($this->dir . '*.php');

        if ($basename) {
            array_walk($files, function (&$v) {
                $v = basename($v);
            });
        }

        return $files;
    }

    public function getClassName($filename)
    {
        $fn = pathinfo($filename, PATHINFO_FILENAME);
        return '_' . str_replace('-', '_', $fn);
    }

    public function getDir()
    {
        return $this->dir;
    }
}
