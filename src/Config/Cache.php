<?php

namespace Sys\Config;

use ErrorException;

final class Cache
{
    private string $cacheFile = STORAGE . 'cache/config.php';
    public ?array $cacheMemory = null;

    public function get($file = null)
    {
        $config = ($file) ? $this->cacheMemory[$file] ?? null : $this->cacheMemory;

        if ($config) {
            return $config;
        }

        if (is_file($this->cacheFile)) {
            $this->cacheMemory = require $this->cacheFile;
            return ($file) ? $this->cacheMemory[$file] ?? null : $cacheMemory;
        }

        return null;
    }

    public function add($file, $config)
    {
        $this->cacheMemory = (is_file($this->cacheFile)) ? require $this->cacheFile : null;
        $this->cacheMemory[$file] = $config;

        $content = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($this->cacheMemory, true) . ';';
        file_put_contents($this->cacheFile, $content);

        try {
            chmod($this->cacheFile, 0777);
        } catch (ErrorException $e) {
            return;
        }
    }

    public function clearCacheFile()
    {
        if (is_file($this->cacheFile)) {
            unlink($this->cacheFile);
        }
    }
}
