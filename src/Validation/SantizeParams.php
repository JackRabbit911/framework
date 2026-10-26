<?php

namespace Az\Validation;

trait SantizeParams
{
    private function santizeParams(array $params, $value): array
    {
        $is_value = false;

        foreach ($params as &$param) {
            if (is_string($param)) {
                $param = trim($param);
            }

            switch ($param) {
                case ':data':
                    $param = $this->data;
                    break;
                case ':value':
                    $param = $value;
                    $is_value = true;
                    break;
                default:
                    if (is_string($param) && strpos($param, ':') === 0) {
                        $key = substr($param, 1);
                        
                        if (array_key_exists($key, $this->data)) {
                            $param = $this->data[$key];
                        }
                    }
            }
        }

        if (!$is_value) {
            array_unshift($params, $value);
        }

        return $params;
    }
}
