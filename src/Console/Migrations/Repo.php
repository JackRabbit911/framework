<?php

declare(strict_types=1);

namespace Sys\Console\Migrations;

class Repo
{
    public function __construct(
        private Model $model,
        private CreateClass $creator
    ){}

    public function get()
    {
        return $this->model->get();
    }

    public function up(array $up)
    {
        $sql = '';
        $data = [];

        foreach ($up as $filename) {
            $class = $this->creator->getClassName($filename);
            $file = CreateClass::DIR . $filename;
            require $file;
            $str = preg_replace('/^([\s\t\r\n]+)|([\s\t\r\n]){2,}/m', '$2', $class::up());
            $sql .= rtrim($str, ';') . ';';
            $data[] = ['name' => $filename];
        }

        $this->model->exec($sql);
        $this->model->insert($data);
        return [count($data), $this->model->get()];
    }
    
    public function down(): array|false
    {
        $filename = $this->model->getLast();

        if (!$filename) {
            return false;
        }

        $class = $this->creator->getClassName($filename);
        $file = CreateClass::DIR . $filename;
        require $file;
        $sql = $class::down();
        $this->model->exec($sql);
        $this->model->delete($filename);

        return [$filename, $this->model->get()];
    }
}
