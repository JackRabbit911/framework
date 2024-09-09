<?php

namespace Sys;

use Psr\Http\Message\UploadedFileInterface;

class UploadedFileSave
{
    protected $path = STORAGE . 'uploads/';
    protected $hashFileName = true;
    protected array|string $clientFilename;
    private $file;
    private int $user_id;

    public function __construct(UploadedFileInterface|array $file, int $user_id)
    {
        $this->file = $file;
        $this->user_id = $user_id;
    }

    public function getClientFilename(): string|array
    {
        return $this->clientFilename;
    }

    public function save(callable|string $arg = null): string|array
    {
        if (is_callable($arg)) {
            if (is_array($this->file)) {
                foreach ($this->file as $file) {
                    $result[] = call_user_func($arg, $file, $this->user_id);
                }
            } else {
                $result = call_user_func($arg, $this->file, $this->user_id);
            }
        } else {
            $result = $this->saveDefault($arg);
        }

        return $result;
    }

    protected function saveDefault($path): string|array
    {
        if (empty($path)) {
            $path = $this->path . $this->user_id . '/';
        }

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        if (!is_writable($path)) {
            chmod($path, 0777);
        }

        if (is_array($this->file)) {
            foreach ($this->file as $file) {
                [$result[], $this->clientFilename[]] = $this->move($file, $path);
            }
        } else {
            [$result, $this->clientFilename] = $this->move($this->file, $path);
        }

        return $result;
    }

    protected function move($file, $path): array
    {
        $origname = $this->santizeFilename($file->getClientFilename());
        $filename = ($this->hashFileName) ? $this->generateFilename($file->getClientFilename()) : $origname;

        $filepath = $path . $filename;
        $file->moveTo($filepath);
        return [$filepath, $origname];
    }

    protected function generateFilename($str): string
    {
        return md5(uniqid() . random_bytes(12) . $str);
    }

    protected function santizeFilename($str): string
    {
        return preg_replace_callback_array([
            '/\s+/' => function($matches) {return '_';},
            '/\b[A-ZА-ЯЁ]+\b$/' => function($matches) {return strtolower($matches[0]);}
        ], $str);
    }
}
