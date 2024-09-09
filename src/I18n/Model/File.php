<?php

namespace Sys\I18n\Model;

final class File implements I18nModelInterface
{
    private array $map = [];
    private array $paths;

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
        $this->paths = findPath('config/i18n/', true);

        foreach ($this->paths as $path) {
            $file = trim($path, '/') . '/' . $lang . '.php';
            if (is_file($file)) {
                $this->map = array_replace($this->map, require $file);
            }
        }
    }
}
