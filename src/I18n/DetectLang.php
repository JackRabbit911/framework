<?php declare(strict_types=1);

namespace Sys\I18n;

use Sys\I18n\Enum\DetectionMethod;
use Psr\Http\Message\ServerRequestInterface;

class DetectLang
{
    private array $langs;
    private int $index;

    public function __construct($langs, $index = 0)
    {
        $this->langs = $langs;
        $this->index = $index;
    }

    public function detectByHeader(string $header): ?string
    {
        $pattern = '/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i';

        preg_match_all($pattern, $header, $matches);
        $langs = [];

        if (count($matches[1])) {
            $langs = array_combine($matches[1], $matches[4]);

            foreach ($langs as $lang => $q) {
                if ($q === '') {
                    $langs[$lang] = 1.0;
                } else {
                    $langs[$lang] = (float) $q;
                }
            }

            arsort($langs, SORT_NUMERIC);
        }

        foreach ($langs as $lang => $q) {
            if (isset($this->langs[$lang])) {
                return $lang;
            }
        }

        return null;
    }

    public function detectBySegment(string $uri): ?string
    {
        $array = explode('/', trim($uri, '/'));
        return $this->getLangFromArray($array);
    }

    public function detectBySubdomain(string $host): ?string
    {
        $array = explode('.', $host);
        return $this->getLangFromArray($array);
    }

    public function detectLang(ServerRequestInterface $request, DetectionMethod $method): string
    {
        $lang = match ($method) {
            $method::Segment => $this->detectBySegment($request->getUri()->getPath()),
            $method::Subdomain => $this->detectBySubdomain($request->getUri()->getHost()),
            default => null,
        };

        if (is_null($lang)) {
            $lang = $this->detectByHeader($request->getHeaderLine('Accept-Language'))
                ?: array_key_first($this->langs) ?? 'en';
        }

        return $lang;
    }

    private function getLangFromArray($array): ?string
    {
        if (isset($array[$this->index]) && isset($this->langs[$array[$this->index]])) {
            return $array[$this->index];
        }

        return null;
    }
}
