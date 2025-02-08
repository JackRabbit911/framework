<?php declare(strict_types = 1);

namespace Sys\Image;

use HttpSoft\Message\UploadedFile;
use Nette\Utils\Image;

class Im
{
    public Image $image;
    public string $file;
    public string $ext;
    public int $format;

    public function __construct(UploadedFile|string $file)
    {
        if ($file instanceof UploadedFile) {
            $str = $file->getStream()->getContents();
            $this->image = Image::fromString($str, $format);
            $this->file = $file->getClientFilename();
            $this->file = strtolower(str_replace(' ', '_', $this->file));
        } else {
            $this->image = Image::fromFile($file, $format);
            $this->file = $file;
        }

        $this->ext = Image::typeToExtension($format);
        $this->format = $format;
    }

    public static function create($file)
    {
        return new self($file);
    }

    public function thumb($width, $height = null)
    {
        if (!$height) {
            $height = $width;
        }

        $this->image->resize($width, $height, Image::ShrinkOnly | Image::Cover);
        return $this;
    }

    public function save($filepath = null, $quality = null, $imageType = null)
    {
        $filepath = $filepath ?: $this->file;

        ['dirname' => $dir, 'filename' => $filename] = pathinfo($filepath);
        $filepath = $dir . '/' . $filename . '.'
            . Image::typeToExtension($this->format);

        $this->image->save($filepath, $quality, $imageType);
    }

    public function send($type = null, ?int $quality = null)
    {
        $type = (($type)) ?: $this->format;
        $this->image->send($type, $quality);
    }
}
