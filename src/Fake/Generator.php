<?php declare(strict_types=1);

namespace Sys\Fake;

use Faker;
use Ottaviano\Faker\Gravatar;

abstract class Generator
{
    const LOCALES = [
        'en' => 'en_US',
        'de' => 'de_DE',
        'ru' => 'ru_RU',
    ];

    const MODES = [
        'monsterid',
        'robohash',
        'wavatar',
    ];

    protected Faker\Generator $faker;
    // protected $model = ModelTable::class;
    // protected $table;
    // private int $memoryLimit = 1000000;

    public function __construct(string $lang)
    {
        $this->faker =  Faker\Factory::create(self::LOCALES[$lang]);
        $this->faker->addProvider(new Gravatar($this->faker));
        $this->faker->addProvider(new FakeProvider($this->faker));
    }
}
