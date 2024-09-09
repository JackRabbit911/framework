<?php

namespace Sys\Config;

use Symfony\Component\Yaml\Yaml;

final class File
{
    private array $paths;

    public function setPaths($paths)
    {
        $this->paths = $paths;
        return $this;
    }

    public function getContents($file)
    {
        $result = [];
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $pattern = ($ext) ? $file : $file . '.{php,yml}';

        foreach ($this->paths as $dir) {
            $list = glob($dir . $pattern, GLOB_BRACE);
            
            if (!empty($list)) {
                $ext = pathinfo($list[0], PATHINFO_EXTENSION);
                $content = ($ext === 'yml') ? Yaml::parseFile($list[0], Yaml::PARSE_CONSTANT) : require $list[0];
                
                $result = (is_array($content)) ? array_merge($content, $result) : $result;
            }
        }

        return (empty($result)) ? null : $result;
    }
}
