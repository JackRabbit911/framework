<?php

declare(strict_types=1);

namespace Sys\Image;

use HttpSoft\Message\UploadedFile;
use Imagick;
use SplFileInfo;
use Traversable;

class Images
{
    private string $dest;
    private array $files;

    public static function create(string $source, string $dest, string $pattern = '*.{jp*g,png,gif,webp}')
    {
        return new self($source, $dest, $pattern);
    }

    public function __construct(string $source, string $dest, string $pattern)
    {
        if (!empty($dest) && !is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $this->files = glob($source . $pattern, GLOB_BRACE);
        $this->dest = $dest;
    }

    public function thumb(int $width, int $height, ?string $format = null)
    {
        $this->exec('thumbnailImage', $width, $height, $format);
    }

    public function avatar(int $width, int $height, ?string $format = null)
    {
        $this->exec('cropThumbnailImage', $width, $height, $format);
    }

    private function exec(string $func, int $width, int $height, ?string $format = null)
    {
        foreach ($this->files as $file) {
            $image = new Imagick($file);

            if (!$format) {
                $_format = strtolower($image->getImageFormat());
            } else {
                $_format = $format;
            }

            $is_animated = $image->getNumberImages() > 1 ?: false;
            $new_filename = $this->dest . pathinfo($file, PATHINFO_FILENAME) . '.' . $_format;

            if ($is_animated) {
                $image = $image->coalesceImages();

                if (($width > 0 && $image->getImageWidth() > $with) || ($height > 0 && $image->getImageHeight() > $height)) {
                    foreach ($image as $frame) {
                        call_user_func([$frame, $func], $width, $height);
                        $frame->setImagePage($width, $height, 0, 0);
                    }
                }

                $image->writeImages($new_filename, true);
            } else {
                if (($width > 0 && $image->getImageWidth() > $with) || ($height > 0 && $image->getImageHeight() > $height)) {
                    call_user_func([$image, $func], $width, $height);
                }
                
                $image->writeImage($new_filename);
            }

            $image->clear();
            $image->destroy();
        }
    }
}
