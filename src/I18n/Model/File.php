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

    private function setMap(string $lang): void
    {
        foreach ($this->paths as $path) {
            $list = glob($path . '/' . $lang . '.{php,yml,yaml}', GLOB_BRACE);

            if (!empty($list)) {
                $ext = pathinfo($list[0], PATHINFO_EXTENSION);
                $content = ($ext === 'php') ? require $list[0] : Yaml::parseFile($list[0]);       
                $this->map = array_replace($this->map, $content);
            }
        }
    }
}
