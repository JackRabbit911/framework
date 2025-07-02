<?php

declare(strict_types=1);

namespace Sys\Console\Migrations;

use Sys\Console\CallApi;

class Migrate
{
    private array $all;
    private array $down;

    public function __construct(private CreateClass $creator)
    {
        $pattern = CreateClass::DIR . '*.php';

        $this->all = array_map(function ($v) {
            return pathinfo($v, PATHINFO_BASENAME);
        }, glob($pattern));

        $this->down = (new CallApi(Repo::class, 'get'))->execute();
    }

    public function test()
    {
        return array_diff($this->all, $this->down);
    }

    public function up()
    {
        $up = array_diff($this->all, $this->down);

        if (!empty($up)) {
            [$count, $down] = (new CallApi(Repo::class, 'up'))->execute(['up' => $up]);
            
            $title = "<fg=green>$count migrations completed</>";
            return $this->show($title, $down);
        } else {
            $title = 'All migrations up is already done';
            return $this->show($title);
        }
    }

    public function down()
    {
        $res = (new CallApi(Repo::class, 'down'))->execute();

        if ($res) {
            $title = "<fg=green>migration {$res[0]} completed</>";
            return $this->show($title, $res[1]);
        } else {
            $title = 'All migrations down is already done';
            return $this->show($title);
        }
    }

    public function show(?string $title = null, ?array $array = null)
    {
        if (!$title) {
            $title = '<fg=white>Migrations list.</> ';
            $title .= '<fg=yellow>completed</> <fg=white>and</> ';
            $title .= '<fg=green>not yet completed</>';
        }
        if (!$array) {
            $array = $this->down;
        }

        $result = array_map(function ($v) use ($array) {
            return in_array($v, $array)
                ? "<comment>$v</comment>"
                : "<info>$v</info>";
        }, $this->all);

        rsort($result);

        return [$title, $result];
    }
}
