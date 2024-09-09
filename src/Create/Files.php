<?php

namespace Sys\Create;

final class Files
{
    private string $appPath2blanks;
    private string $sysPath2blanks;

    public function __construct()
    {
        $this->appPath2blanks = config('common', 'create.blanks');
        $this->sysPath2blanks = __DIR__ . '/blanks/';
    }
    
    public function create(string $path, string $name, array $types, array $paths)
    {
        $success = $errors = $warnings = [];

        foreach ($types as $type) {
            $search = ['{FILENAME}', '{filename}'];
            $replace = [ucfirst($name), $name];
            $file = $path . str_replace($search, $replace, $paths[$type]);

            if (is_file(APPPATH . $file)) {
                $warnings[] = $file;
                continue;
            }

            $info = pathinfo($file);
            $filesname = $info['filename'];
            $dir = $info['dirname'];

            if (!is_dir($dir)) {
                mkdir($dir, 0775, true);
            }
    
            if (!is_writable($dir)) {
                chmod($dir, 0775);
            }
    
            if (!is_dir($dir) || !is_writable($dir)) {
                $errors[] = $dir;
                return false;
            }

            $data = $this->getData($dir, $filesname, $paths['model']);
            $content = render($this->getBlank($type), $data);

            $res = file_put_contents(APPPATH . $file, $content);

            if ($res) {
                $success[] = $file;
            } else {
                $errors[] = $file;
            }
        }

        return [
            'success' => $success,
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    private function getBlank($type)
    {
        $file = $this->appPath2blanks . $type . '.php';

        if (!is_file($file)) {
            $file = $this->sysPath2blanks . $type . '.php';
        }

        return $file;
    }

    private function getData($dir, $name, $model)
    {
        $namespace = ucfirst(str_replace('/', '\\', $dir));

        $info = pathinfo($model);

        $search = ['{FILENAME}', '{filename}', '/'];
        $replace = [ucfirst($name), $name, '\\'];

        $model_classname = ucfirst(str_replace($search, $replace, $info['filename']));
        $model_namespace = ucfirst(ltrim(str_replace($search, $replace, $info['dirname']), '\\'));
        $model_namespace .= '\\' . $model_classname;

        return [
            'php' => '<?php declare(strict_types=1);' . PHP_EOL,
            'namespace' => $namespace,
            'classname' => $name,
            'model_namespace' => $model_namespace,
            'model_classname' => $model_classname,
        ];
    }
}
