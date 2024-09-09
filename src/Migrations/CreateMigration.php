<?php

namespace Sys\Migrations;

class CreateMigration
{
    use ClassNameTrait;

    private string $dir;

    public function create(string $pattern, string $path = ''): array
    {
        $this->dir = config('common', 'migrations_dir') ?? CONFIG . 'migrations/';

        $pattern = preg_replace('/\s+/', ' ', $pattern);

        if ($path === '' && str_contains($pattern, ' ')) {
            [$pattern, $path] = explode(' ', $pattern);
        }

        $dateFormat = date('Y-m-d_H-i-s');
        $filename = $dateFormat . '_' . $pattern;
        $classname = $this->getClassName($filename);
        $filename .= '.php';

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

        return [$result, $filename];
    }
}
