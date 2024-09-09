<?php declare(strict_types=1);

namespace Sys\Fake;

use Faker\Provider\Base;

class FakeProvider extends Base
{
    public static function json(null|array|callable $data = null): string|null
    {
        if (is_callable($data)) {
            $data = $data();
        }

        return (empty($data)) ? null 
            : json_encode($data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}
