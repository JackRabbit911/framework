<?php

namespace Sys\Helper;

use Behat\Transliterator\Transliterator;

final class Text extends Transliterator
{
    public function snakeToCamel($string, $capitalizeFirstCharacter = false)
    {
        return $this->dashesToCamelCase($string, '_', $capitalizeFirstCharacter);
    }

    public function kebabToCamel($string, $capitalizeFirstCharacter = false)
    {
        return $this->dashesToCamelCase($string, '-', $capitalizeFirstCharacter);
    }

    public function camelToSnake($string)
    {
        return strtolower(preg_replace('/(?<=.)[A-Z]/', '_$0', $string));
    }

    public function camelToKebab($string)
    {
        return strtolower(preg_replace('/(?<=.)[A-Z]/', '-$0', $string));
    }

    public function fileToClassName($string, $remove_substr = APPPATH)
    {
        $string = str_replace([$remove_substr, '/', '.php'], ['', '\\', ''], $string);
        return ucwords($string, '\\');
    }

    public function strToSlug($string, $separator = '-')
    {
        $search = [',', ' '];
        $replace = ['', $separator];
        return strtolower(str_replace($search, $replace, $string));
    }

    public function catStr($str, $count_words)
    {
        if (str_word_count($str) <= $count_words) {
            return $str;
        }

        $array = explode(' ', $str, $count_words + 1);
        unset($array[array_key_last($array)]);
        return implode(' ', $array);
    }

    private function dashesToCamelCase($string, $separator, $capitalizeFirstCharacter = false) 
    {
        $str = str_replace($separator, '', ucwords($string, $separator));
        return (!$capitalizeFirstCharacter) ? lcfirst($str) : $str;
    }
}
