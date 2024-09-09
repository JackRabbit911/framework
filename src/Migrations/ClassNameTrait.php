<?php

namespace Sys\Migrations;

use Sys\Migrations\ModelMigrations;

trait ClassNameTrait
{
    private function getClassName($filename)
    {
        return preg_replace(['/^[\D\/]*|-/', '/.php$/'], ['_', ''], $filename);
    }
}
