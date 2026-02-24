<?php

declare(strict_types=1);

namespace Sys\Console\Migrations;

class CreateClass
{
    const NO_BLANK = 1;
    const NO_DIR = 2;
    const DIR = APPPATH . 'common/migrations/';

    public function create(string $table, string $action = 'create')
    {
        $filename = $this->getFileName($table, $action);
        $data = $this->getData($filename, $table, $action);
        $content = $this->createContent($data);

        if (!$content) {
            return self::NO_BLANK;
        }

        if (!is_dir(self::DIR)) {
            mkdir(self::DIR, 0775, true);
        }
        
        if (!is_writable(self::DIR)) {
            chmod(self::DIR, 0775);
        }
        
        if (!is_dir(self::DIR) || !is_writable(self::DIR)) {            
            return self::NO_DIR;
        }

        file_put_contents(self::DIR . $filename, $content);

        return $filename;
    }

    public function getClassName(string $filename)
    {
        return preg_replace(['/^[\D\/]*|-/', '/.php$/'], ['_', ''], $filename);
    }

    private function getFileName(string $table, string $action)
    {
        $dateFormat = date('Y-m-d_H-i-s');
        return $dateFormat . '_' . $action . '-table-' . $table . '.php';
    }

    private function getData(string $filename, string $table, string $action)
    {
        $classname = $this->getClassName($filename);
        $up = strtoupper($action) . ' TABLE `' . $table . '`';

        $action_down = ($action === 'alter') ? 'ALTER' : 'DROP';
        $down = $action_down . ' TABLE `' . $table . '`';

        return [
            'php' => '<?php',
            'classname' => $classname,
            'up' => $up,
            'down' => $down,
        ];
    }

    private function createContent($data)
    {
        $blank = FRAMEWORK . 'Console/blanks/migration.php';

        if (!is_file($blank)) {
            return false;
        }

        return render($blank, $data);
    }
}
