<?php

namespace Sys\Config;

final class Config
{
    private array $paths = [CONFIG];
    private Cache $cache;
    private File $repo;

    public ?bool $isCache;
    

    public function __construct(Cache $cache, File $repo)
    {
        $this->paths += glob(APPPATH . '**{\/**,}/config/', GLOB_BRACE|GLOB_ONLYDIR);
        $this->paths = array_unique($this->paths);
        // $this->paths += glob(APPPATH . '*{\/src,}/config/', GLOB_BRACE|GLOB_ONLYDIR);
        $this->cache = $cache;
        $this->repo = $repo;

        if (!isset($this->isCache)) {
            $this->isCache = IS_CACHE;
        }
    }

    public function get($file, $path = null, $default = null)
    {
        $config = ($this->isCache) ? $this->cache->get($file) : null;

        if (!$config) {
            $config = $this->repo->setPaths($this->paths)
                ->getContents($file);
        }

        if ($config) {
            if ($this->isCache) {
                $this->cache->add($file, $config);
            }

            return ($path) ? dot($config, $path, $default) : $config;
        }

        return $default;
    }

    public function addPath($path) {
        $this->paths[] = rtrim($path, '/') . '/';
        return $this;
    }

    public function enable($enable = true)
    {
        $this->isCache = $enable;
        return $this;
    }

    public function getEnabled()
    {
        return $this->isCache;
    }

    private function path2arr($path, $value)
    {
        $keys = array_reverse(explode('/', $path));
        $res = [];

        foreach ($keys as $k => $key) {
            if ($k === 0) {
                $res[$key] = $value;
            } else {
                $res[$key] = $res;
                unset($res[$keys[$k - 1]]);
            }
        }

        return $res;
    }
}
