<?php declare(strict_types=1);

namespace Az\Route;

use InvalidArgumentException;

class Matcher
{
    private const PLACEHOLDER = '~(?:\{([\w\-]+|[^{}\?]+\?)\})~';
    private const DELIMETER_PLACEHOLDER = '~(?:([^\w\{\}]+)\{[\w\/\-\&\+]+\?\})~i';
    private const DEFAULT_TOKEN = '\w+';
    private const ROOT_PATH_PATTERN = '\/?';

    private array $delimeters = [];

    public function match(string $pattern, string $path, array $tokens)
    {
        $pattern = $this->santizePattern($pattern, $tokens);
        $path = rawurldecode(rtrim($path, '/'));

        if (preg_match('~^' . $pattern . '$~i', $path, $matches)) {
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            return array_filter($params);
        }

        return false;
    }

    public function path(string $name, string $pattern, array $params = []): string
    {
        $path = preg_replace_callback(self::PLACEHOLDER, function($matches) use ($name, $pattern, $params) {
            $parameter = $matches[1];
           
            if (substr((string) $parameter, -1) === '?') {
                $parsedOptionalParameters = $this->parsePatternOptional($parameter);
                $substr = '';

                foreach ($parsedOptionalParameters['params'] as $k => $p) {
                    $delimeter = $parsedOptionalParameters['delimeters'][$k] ?? '/';
                    $substr .= $params[$p] ?? '';
                    $substr .= $delimeter;
                }

                return str_replace('__invoke', '', $substr);
            } elseif (!isset($params[$parameter])) {
                throw new InvalidArgumentException(
                    sprintf('The token "%s" is required! Route "%s", "%s"'
                    , $parameter, $name, $pattern));
            }

            return $params[$parameter];
        }, $pattern);

        $path = preg_replace('~\/{2,}~', '/', $path);

        return $path === '/' ? $path : rtrim($path, '/');
    }

    private function santizePattern(string $pattern, array $tokens)
    {
        $pattern = str_replace(['+', '~'], ['\+', '\~'], $pattern);
        $pattern = '/' . trim($pattern, '/');

        $pattern = preg_replace_callback(self::DELIMETER_PLACEHOLDER, function (array $m): string {
            $this->delimeters[] = $m[1];
            return substr($m[0], strlen($m[1]));
        }, $pattern);

        return !($pattern === '' || $pattern === '/') // !is root path
            ? preg_replace_callback(self::PLACEHOLDER, function (array $matches) use ($tokens): string {
                $parameter = $matches[1];

                return (substr((string) $parameter, -1) === '?') // is optional parameter
                    ? $this->getOptionalReplacement($parameter, $tokens)
                    : $this->getReplacement($parameter, $tokens)
                ;
            }, $pattern)
            : self::ROOT_PATH_PATTERN;
    }

    private function getReplacement(string $parameter, array $tokens): string
    {
        return '(?P<' . $parameter . '>' . ($tokens[$parameter] ?? self::DEFAULT_TOKEN) . ')';
    }

    private function getOptionalReplacement(string $parameter, array $tokens): string
    {
        $head = $tail = '';

        $parsedOptionalParameters = $this->parsePatternOptional($parameter);

        foreach ($parsedOptionalParameters['params'] as $k => $parameter) {
            $delimeter = ($k === 0)
                ? array_shift($this->delimeters)
                : array_shift($parsedOptionalParameters['delimeters']);
            $head .= '(?:'. $delimeter . $this->getReplacement($parameter, $tokens);
            $tail .= ')?';
        }

        return $head . $tail;
    }

    private function parsePatternOptional(string $parameter): array
    {
        $parameter = rtrim($parameter, '?');

        $array = preg_split('~(\W+)~', $parameter, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        foreach ($array as $item) {
            if (ctype_punct($item)) {
                $result['delimeters'][] = $item;
            } else { 
                $result['params'][] = $item;
            }
        }

        return array_filter($result);
    }
}
