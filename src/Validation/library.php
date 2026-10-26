<?php

declare(strict_types=1);

namespace Az\Validation;

function flattenDot($array, $prefix = '')
{
    $res = [];

    foreach ($array as $key => $value) {
        $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
        if (is_array($value)) {
            if (array_is_list($value)) {
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $res = array_merge_recursive($res, flattenDot($item, $newKey));
                    } else {
                        $res[$newKey][] = $item;
                    }
                }
            } else {
                $res = array_merge_recursive($res, flattenDot($value, $newKey));
            }
        } else {
            $res[$newKey] = $value;
        }
    }

    return $res;
}

function flattenBracket($data)
{
    $flatten = function ($data, $prefix = null) use (&$flatten) {
        $result = [];

        foreach ($data as $key => $value) {
            if ($prefix) {
                $key = $prefix . '[' . $key . ']';
            }

            if (is_array($value) && !array_is_list($value)) {
                $result = array_merge($result, $flatten($value, $key));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    };

    $data = $flatten($data);

    return $data;
}
