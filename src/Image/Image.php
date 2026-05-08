<?php

declare(strict_types=1);

namespace Sys\Image;

use HttpSoft\Message\UploadedFile;
use Imagick;

class Image
{
    private Imagick $im;
    private string $originName;
    public bool $isAnimated;

    public static function create(UploadedFile | string $file)
    {
        return new self($file);
    }

    public function __construct(UploadedFile | string $file)
    {
        $this->im = new Imagick();

        if ($file instanceof UploadedFile) {
            $this->originName = $file->getClientFilename();
            $blob = $file->getStream()->getContents();
            $this->im->readImageBlob($blob);
        } else {
            $this->originName = $file;
            $this->im->readImage($file);
        }

        $this->isAnimated = $this->im->getNumberImages() > 1 ?: false;
    }

    public function thumb(int $width, int $height): self
    {
        $this->exec('thumbnailImage', $width, $height);
        return $this;
    }

    public function avatar($width, $height): self
    {
        $this->exec('cropThumbnailImage', $width, $height);
        return $this;
    }

    public function show(?string $format = null)
    {
        if (!$format) {
            $format = $this->im->getImageFormat();
        }

        $this->im->setImageFormat($format);
        $content_type = 'image/' . strtolower($format);

        header("Content-Type: $content_type");
        echo $this->isAnimated
            ? $this->im->getImagesBlob()
            : $this->im->getImageBlob();

        $this->im->clear();
        $this->im->destroy();
    }

    public function save(?string $filename = null, ?string $format = null): self
    {
        if (!$filename) {
            $filename = $this->originName;
        }

        if (!$format) {
            $format = strtolower($this->im->getImageFormat());
        }

        $this->im->setImageFormat($format);
        $this->isAnimated
            ? $this->im->writeImages($filename, true)
            : $this->im->writeImage($filename);

        return $this;
    }

    private function exec(string $func, int $width, int $height)
    {
        if ($this->isAnimated) {
            $this->im = $this->im->coalesceImages();

            if (($width > 0 && $this->im->getImageWidth() > $width) || ($height > 0 && $this->im->getImageHeight() > $height)) {
                foreach ($this->im as $frame) {
                    call_user_func([$frame, $func], $width, $height);
                    $frame->setImagePage($width, $height, 0, 0);
                }
            }
        } else {
            if (($width > 0 && $this->im->getImageWidth() > $width) || ($height > 0 && $this->im->getImageHeight() > $height)) {
                call_user_func([$this->im, $func], $width, $height);
            }
        }
    }
}
