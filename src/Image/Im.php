<?php declare(strict_types = 1);

namespace Sys\Image;

use Nette\Utils\Image;

class Im
{
    private Image $image;
    private string $file;
    private int $format;

    public function __construct($file)
    {
        $this->image = Image::fromFile($file, $format);
        $this->file = $file;
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
        $this->image->save($filepath, $quality, $imageType);
    }

    public function send($type = null, ?int $quality = null)
    {
        $type = (($type)) ?: $this->format;
        $this->image->send($type, $quality);
    }
}
