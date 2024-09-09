<?php

namespace Sys\Migrations\Commands;

use Sys\Migrations\ModelMigrations;
use Sys\Migrations\ClassNameTrait;
use Sys\Migrations\UpDownTrait;
use Sys\Console\CallApi;

final class UpDown extends MigrationsCommandAbstract
{
    use UpDownTrait;
    use ClassNameTrait;

    protected function configure()
    {
        $this->addArgument('array', 'list of files or action up/down and path', []);
    }

    public function execute($array)
    {
        if (empty($array)) {
            $this->warning('Too few arguments for this command');
        }

        $array = (is_array($array)) ? $array : [$array];
        
        array_walk($array, function (&$v) {
            $v = strtolower($v);
        });

        try {
            if (array_intersect($array, ['up', 'down'])) {
                $result = $this->doUpDown($array);
            } else {
                $result = $this->files($array);
            }
        } catch(\UnexpectedValueException $e) {
            $this->warning();
        }

        if (!$result[0] || empty($result[1])) {
            $this->warning();
        }

        $data['sql'] = $result[0];
        $data['insert'] = $result[1]['insert'] ?? [];
        $data['delete'] = $result[1]['delete'] ?? [];

        $res = (new CallApi(ModelMigrations::class, 'do'))->execute($data);
    
        if ($res) {
            $this->climate->lightGreen()->out('Migrations was completed successfully!');
        }
    }

    private function doUpDown($array)
    {
        if (in_array('down', $array)) {
            unset($array[array_search('down', $array)]);
            $array = array_values($array);
            $path = $array[0] ?? null;
            return $this->down($path ?? null);
        }

        unset($array[array_search('up', $array)]);
        $array = array_values($array);
        $path = $array[0] ?? null;
        return $this->up($path);
    }

    private function up($path)
    {
        $data = [];
        $this->setUpDown($path);

        if (empty($this->up)) {
            $this->climate->out('All migrations up is already done');
            exit;
        }

        foreach (array_reverse($this->up) as $fn => $path) {
            [$sql[], $action_data] = $this->getSqlData($path, $fn, 'up');
            $data['insert'][] = $action_data['up'];
        }

        $sql = (isset($sql)) ? implode(';', $sql) : null;

        return [$sql, $data];
    }

    private function down($path)
    {
        $data = [];
        $this->setUpDown($path);

        if (empty($this->down)) {
            $this->climate->out('All migrations up is already done');
            exit;
        }

        $fn = array_key_first($this->down);
        $path = $this->down[$fn];

        [$sql, $action_data] = $this->getSqlData($path, $fn, 'down');

        $data['delete'][] = $action_data['down'];
        return [$sql, $data];
    }

    private function files($files)
    {
        $data = [];
        $this->setUpDown(null);

        foreach ($files as $fn) {
            if (array_key_exists($fn, $this->up)) {
                $func = 'up';
                $path = $this->up[$fn];
                [$sql[], $action_data] = $this->getSqlData($path, $fn, 'up');
                $data['insert'][] = $action_data['up'];
            } elseif (array_key_exists($fn, $this->down)) {
                $func = 'down';
                $path = $this->down[$fn];
                [$sql[], $action_data] = $this->getSqlData($path, $fn, 'down');
                $data['delete'][] = $action_data['down'];
            }
        }

        $sql = (isset($sql)) ? implode(';', $sql) : null;
        return [$sql, $data];
    }

    private function getSqlData($path, $fn, $func)
    {
        $file = str_replace('//', '/', $this->dir . $path . '/' . $fn);
        $class = $this->getClassName($file);
        require_once $file;

        $sql = rtrim((new $class)->$func(), ';');

        $data[$func] = [
            'name' => $fn,
            'path' => $path,
        ];

        return [$sql, $data];
    }

    private function warning(string $msg = 'Arguments is incorrect')
    {
        $msg = "<light_red>WARNING!</light_red> $msg";
        $this->climate->out($msg);
        exit;
    }
}
