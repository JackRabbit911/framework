<?php

namespace Sys\Console;

use Throwable;

final class Argv
{
    public array $setArgs = [];
    public array $setOpts = [];
    public array $aliases;

    public function addArgument(string $name, string $desc = '', mixed $default = null)
    {
        $this->setArgs[] = [
            'name' => $name,
            'desc' => $desc,
            'default' => $default,
        ];
    }

    public function addOption(string|array $name, string $desc = '', array $default = [true, false])
    {
        if (is_array($name)) {
            $alias = $name[1] ?? null;
            $name = $name[0];
        }

        $this->setOpts[$name] = [
            'desc' => $desc,
            'yes' => $default[0],
            'no' => $default[1],
        ];

        if (isset($alias)) {
            $this->aliases[$alias] = $name;
        }
    }

    public function parse($argv)
    {
        foreach ($argv as $value) {
            if (str_starts_with($value, '--')) {
                $opts_values[] = $value;
            } elseif (str_starts_with($value, '-')) {
                $opts_values = array_map(fn($v) => '-' . $v, str_split(ltrim($value, '-')));
            } else {
                $args_values[] = $value;
            }
        }

        $args = $this->parseArguments($args_values ?? []);
        $opts = $this->parseOptions($opts_values ?? []);

        return [$args, $opts];
    }

    public function getDescription($name, $array_name = 'args')
    {
        if ($array_name = 'args') {
            foreach ($this->setArgs as $item) {
                if ($item['name'] === $name) {
                    return $item['desc'] ?? '';
                }
            }

            return '';
        } else {
            return $this->setOpts[$name]['desc'] ?? '';
        }
    }

    private function parseArguments($args_values)
    {
        $last_arg_key = array_key_last($this->setArgs);

        foreach ($args_values as $key => $value) {

            if (str_contains($value, '=')) {
                [$name, $value] = explode('=', $value, 2);
            } 

            if ($key < $last_arg_key) {
                $name = $this->setArgs[$key]['name'];
                $args[$name] = $value;
            } else {
                if ($last_arg_key !== null) {
                    $name = $this->setArgs[$last_arg_key]['name'];
                }

                if (is_array($this->setArgs[$last_arg_key]['default'])) {
                    if (isset($args[$name]) && is_string($args[$name])) {
                        $args[$name] = [$args[$name]];
                    }
                    $args[$name][] = $value;
                } elseif (!isset($args[$name])) {
                    $args[$name] = $value;
                }
            }
        }

        foreach ($this->setArgs as $item) {
            if (array_key_exists($item['name'], $args ?? [])) {
                continue;
            }

            $args[$item['name']] = $item['default'];
        }

        return $args ?? [];
    }

    private function parseOptions($opts_values)
    {
        $opts = [];

        foreach ($opts_values as $name) {

            if (str_contains($name, '=')) {
                [$name, $value] = explode('=', $name, 2);
            }

            $name = ltrim($name, '-');
            $name = $this->aliases[$name] ?? $name;

            $opts[$name] = $value ?? $this->setOpts[$name]['yes'] ?? true;
        }

        foreach ($this->setOpts as $name => $item) {

            if (array_key_exists($name, $opts)) {
                continue;
            }

            $opts[$name] = $this->setOpts[$name]['no'] ?? false;
        }

        return $opts ?? [];
    }
}
