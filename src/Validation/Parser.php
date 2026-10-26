<?php

namespace Az\Validation;

final class Parser
{
    use SantizeParams;

    public function parse($rule): array
    {
        foreach ($rule as &$array) {
            $array = $this->sparse($array['handler'], $array['params']);
        }
        
        array_walk_recursive($rule, function ($item) use (&$result) {
            $result[] = $item;
        });

        return $result ?: [];
    }

    private function sparse($handler, $params)
    {
        if (is_string($handler)) {
            if (str_contains($handler, '|')) {
                foreach (explode('|', str_replace(' ', '', $handler)) as $item) {
                    $res[] = $this->parseStr($item, $params);
                }

                return $res;
            } else {
                return $this->parseStr($handler, $params);
            }

        }

        $inverse = $this->isInverse($handler);

        return (object) ['handler' => $handler, 'params' => $params, 'inverse' => $inverse];
    }

    private function parseStr($handler, $params = [])
    {
        if (strpos($handler, ')', -1) === strlen($handler) - 1) {
            $pattern = '/\((.*)\)/';
            if (preg_match($pattern, $handler, $match))
                if ($match[1]) $params = array_merge(explode(',', $match[1]), $params);

            $handler = preg_replace($pattern, '', $handler);
        }

        $inverse = $this->isInverse($handler);

        return (object) ['handler' => $handler, 'params' => $params, 'inverse' => $inverse];
    }

    private function isInverse($string)
    {
        $inverse = false;

        if (is_string($string) && str_starts_with($string, '!')) {
            $string = substr($string, 1);
            $inverse = true;
        }

        return $inverse;
    }
}
