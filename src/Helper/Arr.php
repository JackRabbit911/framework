<?php

declare(strict_types=1);

namespace Sys\Helper;

class Arr
{
    public function flatten(array $array, string $separator = '.', string $prefix = ''): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value)) {
                if (array_is_list($value)) {
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            $result = array_merge_recursive($result, $this->flatten($item, $separator, $newKey));
                        } else {
                            $result[$newKey][] = $item;
                        }
                    }
                } else {
                    $result = array_merge_recursive($result, $this->flatten($value, $separator, $newKey));
                }
            } else {
                $result[$newKey] = $value;
            }
        }

        return $result;
    }

    public function unflatten(array $flattenedArray, string $pattern = '[\[|\]|\.]'): array
    {
        $result = [];

        foreach ($flattenedArray as $key => $value) {
            $parts = preg_split($pattern, $key, -1, PREG_SPLIT_NO_EMPTY);
            $current = &$result;

            foreach ($parts as $part) {
                if (!isset($current[$part]) || !is_array($current[$part])) {
                    $current[$part] = [];
                }
                $current = &$current[$part];
            }
            $current = $value;
        }

        return $result;
    }

    public function preparePost($array)
    {
        array_walk_recursive($array, function (&$value) {
            if (is_string($value)) {
                $value = trim($value);

                if (is_numeric($value)) {
                    if (str_contains($value, '.')) {
                        settype($value, 'float');
                    } else {
                        settype($value, 'integer');
                    }
                }
            }

            if ($value !== '') {
                $value = match (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) {
                    false => 0,
                    true => 1,
                    default => $value,
                };
            }
        });

        return $array;
    }
}
