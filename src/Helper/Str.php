<?php

namespace Sys\Helper;

use function Symfony\Component\String\u;
use Symfony\Component\String\Slugger\AsciiSlugger;

class Str
{
    public function __call(string $name, array $args)
    {
        return u($args[0])->$name();
    }

    public function u(string $string)
    {
        return u($string);
    }

    public function fileToClassName($string, $remove_substr = APPPATH)
    {
        $string = str_replace([$remove_substr, '/', '.php'], ['', '\\', ''], $string);
        return ucwords($string, '\\');
    }

    public function strWordCount($str)
    {
        return preg_match_all('/\p{L}+/u', $str, $matches);
    }

    public function cat($str, $count_words)
    {
        if ($this->strWordCount($str) <= $count_words) {
            return $str;
        }

        $array = explode(' ', $str, $count_words + 1);
        unset($array[array_key_last($array)]);
        return implode(' ', $array);
    }

    public function slug(string $str, string $separator = '-')
    {
        $slugger = new AsciiSlugger();
        return strtolower($slugger->slug($str, $separator));
    }
}
