<?php

namespace Sys\I18n\Model;

use Symfony\Component\Yaml\Yaml;

class File implements I18nModelInterface
{
    private array $map = [];
    private array $paths = [];

    public function __construct(array|string $paths = [])
    {
        $this->paths = (is_string($paths)) ? [$paths] : $paths;
    }

    public function get(string $lang, string $str, array $values = []): string
    {
        if (empty($this->map)) {
            $this->setMap($lang);
        }
        
        return $this->map[$str] ?? $str;
    }

    public function addPath(string $path): void
    {
        array_push($this->paths, $path);
    }

    public function getMap(string $lang, array $filter, ?string $path = null)
    {
        $new_map = function ($filter, $map) {
            foreach ($filter as $key) {
                $result[$key] = $map[$key] ?? $key;
            }

            return $result;
        };

        if ($path) {
            $map = $this->getMapFromFile($lang, $path);
            return $new_map($filter, $map);
        }

        if (empty($this->map)) {
            $this->setMap($lang);
        }

        if (empty($filter)) {
            return $this->map;
        }

        return $new_map($filter, $this->map);
    }

    private function setMap(string $lang): void
    {
        foreach ($this->paths as $path) {
            $this->map = array_replace($this->map, $this->getMapFromFile($lang, $path));
        }
    }

    private function getMapFromFile(string $lang, string $path)
    {
        $list = glob($path . '/' . $lang . '.{php,yml,yaml}', GLOB_BRACE);

        if (!empty($list)) {
            $ext = pathinfo($list[0], PATHINFO_EXTENSION);
            return ($ext === 'php') ? require $list[0] : Yaml::parseFile($list[0]);       
        }

        return [];
    }
}
