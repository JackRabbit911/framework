<?php

declare(strict_types=1);

namespace Tests\Az\Helper;

use PHPUnit\Framework\TestCase;
use Sys\Helper\Arr;

final class ArrTest extends TestCase
{
    private Arr $arr;

    protected function setUp(): void
    {
        $this->arr = new Arr();
    }

    // ── flatten ──────────────────────────────────────────────────────

    public function testFlattenSimpleArray(): void
    {
        $result = $this->arr->flatten([
            'name' => 'John',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals([
            'name' => 'John',
            'email' => 'john@example.com',
        ], $result);
    }

    public function testFlattenNestedArray(): void
    {
        $result = $this->arr->flatten([
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ]);

        $this->assertEquals([
            'user.name' => 'John',
            'user.email' => 'john@example.com',
        ], $result);
    }

    public function testFlattenDeeplyNestedArray(): void
    {
        $result = $this->arr->flatten([
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ]);

        $this->assertEquals([
            'level1.level2.level3' => 'value',
        ], $result);
    }

    public function testFlattenWithCustomSeparator(): void
    {
        $result = $this->arr->flatten(
            ['user' => ['name' => 'John']],
            separator: '_'
        );

        $this->assertEquals([
            'user_name' => 'John',
        ], $result);
    }

    public function testFlattenWithCustomSeparatorDeep(): void
    {
        $result = $this->arr->flatten(
            ['a' => ['b' => ['c' => 'deep']]],
            separator: '__'
        );

        $this->assertEquals([
            'a__b__c' => 'deep',
        ], $result);
    }

    public function testFlattenWithPrefix(): void
    {
        $result = $this->arr->flatten(
            ['name' => 'John'],
            prefix: 'user'
        );

        $this->assertEquals([
            'user.name' => 'John',
        ], $result);
    }

    public function testFlattenWithListArray(): void
    {
        $result = $this->arr->flatten([
            'tags' => ['php', 'javascript', 'ruby'],
        ]);

        $this->assertEquals([
            'tags' => ['php', 'javascript', 'ruby'],
        ], $result);
    }

    public function testFlattenWithMixedListArray(): void
    {
        $result = $this->arr->flatten([
            'user' => [
                'tags' => ['php', 'js'],
                'name' => 'John',
            ],
        ]);

        $this->assertEquals([
            'user.tags' => ['php', 'js'],
            'user.name' => 'John',
        ], $result);
    }

    public function testFlattenWithListOfArrays(): void
    {
        $result = $this->arr->flatten([
            'items' => [
                ['id' => 1, 'name' => 'A'],
                ['id' => 2, 'name' => 'B'],
            ],
        ]);

        $this->assertEquals([
            'items.id' => [1, 2],
            'items.name' => ['A', 'B'],
        ], $result);
    }

    public function testFlattenWithNumericKeys(): void
    {
        // строковые числовые ключи (не list) — идёт через else branch
        $result = $this->arr->flatten([
            'first' => ['a' => 1],
            'second' => ['b' => 2],
        ]);

        $this->assertEquals([
            'first.a' => 1,
            'second.b' => 2,
        ], $result);
    }

    public function testFlattenWithIntegerListKeys(): void
    {
        // integer-ключи в списке (array_is_list = true)
        // каждый элемент списка — массив, рекурсия даёт отдельные ключи
        $result = $this->arr->flatten([
            ['id' => 1, 'name' => 'A'],
            ['id' => 2, 'name' => 'B'],
        ]);

        $this->assertEquals([
            '0.id' => 1,
            '0.name' => 'A',
            '1.id' => 2,
            '1.name' => 'B',
        ], $result);
    }

    public function testFlattenEmptyArray(): void
    {
        $result = $this->arr->flatten([]);

        $this->assertEquals([], $result);
    }

    public function testFlattenWithMixedTypes(): void
    {
        $result = $this->arr->flatten([
            'string' => 'text',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
        ]);

        $this->assertEquals([
            'string' => 'text',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
        ], $result);
    }

    public function testFlattenComplexNestedStructure(): void
    {
        $result = $this->arr->flatten([
            'user' => [
                'profile' => [
                    'name' => 'John',
                    'age' => 30,
                ],
                'settings' => [
                    'theme' => 'dark',
                    'notifications' => true,
                ],
            ],
        ]);

        $this->assertEquals([
            'user.profile.name' => 'John',
            'user.profile.age' => 30,
            'user.settings.theme' => 'dark',
            'user.settings.notifications' => true,
        ], $result);
    }

    // ── unflatten ────────────────────────────────────────────────────

    public function testUnflattenSimple(): void
    {
        $result = $this->arr->unflatten([
            'name' => 'John',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals([
            'name' => 'John',
            'email' => 'john@example.com',
        ], $result);
    }

    public function testUnflattenNested(): void
    {
        $result = $this->arr->unflatten([
            'user.name' => 'John',
            'user.email' => 'john@example.com',
        ]);

        $this->assertEquals([
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ], $result);
    }

    public function testUnflattenDeeplyNested(): void
    {
        $result = $this->arr->unflatten([
            'level1.level2.level3' => 'value',
        ]);

        $this->assertEquals([
            'level1' => [
                'level2' => [
                    'level3' => 'value',
                ],
            ],
        ], $result);
    }

    public function testUnflattenWithBrackets(): void
    {
        $result = $this->arr->unflatten([
            'items[0].id' => 1,
            'items[0].name' => 'A',
            'items[1].id' => 2,
            'items[1].name' => 'B',
        ]);

        $this->assertEquals([
            'items' => [
                0 => [
                    'id' => 1,
                    'name' => 'A',
                ],
                1 => [
                    'id' => 2,
                    'name' => 'B',
                ],
            ],
        ], $result);
    }

    public function testUnflattenWithBracketsAndDots(): void
    {
        $result = $this->arr->unflatten([
            'user.settings[theme]' => 'dark',
            'user.settings[notifications]' => true,
        ]);

        $this->assertEquals([
            'user' => [
                'settings' => [
                    'theme' => 'dark',
                    'notifications' => true,
                ],
            ],
        ], $result);
    }

    public function testUnflattenEmptyArray(): void
    {
        $result = $this->arr->unflatten([]);

        $this->assertEquals([], $result);
    }

    public function testUnflattenRoundtripWithFlatten(): void
    {
        $original = [
            'user' => [
                'profile' => [
                    'name' => 'John',
                    'age' => 30,
                ],
                'settings' => [
                    'theme' => 'dark',
                ],
            ],
        ];

        $flattened = $this->arr->flatten($original);
        $restored = $this->arr->unflatten($flattened);

        $this->assertEquals($original, $restored);
    }

    public function testUnflattenWithCustomPattern(): void
    {
        // custom pattern используется для preg_split
        $result = $this->arr->unflatten(
            ['user_name' => 'John', 'user_email' => 'john@example.com'],
            pattern: '/_/'
        );

        $this->assertEquals([
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
        ], $result);
    }

    // ── preparePost ──────────────────────────────────────────────────

    public function testPreparePostTrimsStrings(): void
    {
        $result = $this->arr->preparePost([
            'name' => '  John  ',
            'email' => '  john@example.com  ',
        ]);

        $this->assertEquals([
            'name' => 'John',
            'email' => 'john@example.com',
        ], $result);
    }

    public function testPreparePostConvertsIntegers(): void
    {
        $result = $this->arr->preparePost([
            'age' => '30',
            'count' => '42',
        ]);

        $this->assertEquals([
            'age' => 30,
            'count' => 42,
        ], $result);
    }

    public function testPreparePostConvertsFloats(): void
    {
        $result = $this->arr->preparePost([
            'price' => '19.99',
            'weight' => '3.14',
        ]);

        $this->assertEquals([
            'price' => 19.99,
            'weight' => 3.14,
        ], $result);
    }

    public function testPreparePostConvertsBooleans(): void
    {
        $result = $this->arr->preparePost([
            'active' => '1',
            'disabled' => '0',
            'yes' => 'true',
            'no' => 'false',
            'on' => 'on',      // filter_var распознаёт 'on' как true
            'off' => 'off',    // filter_var распознаёт 'off' как false
        ]);

        $this->assertEquals([
            'active' => 1,
            'disabled' => 0,
            'yes' => 1,
            'no' => 0,
            'on' => 1,
            'off' => 0,
        ], $result);
    }

    public function testPreparePostNestedArray(): void
    {
        $result = $this->arr->preparePost([
            'user' => [
                'name' => '  John  ',
                'age' => '25',
                'active' => 'true',
            ],
        ]);

        $this->assertEquals([
            'user' => [
                'name' => 'John',
                'age' => 25,
                'active' => 1,
            ],
        ], $result);
    }

    public function testPreparePostPreservesNonNumericStrings(): void
    {
        $result = $this->arr->preparePost([
            'name' => 'John Doe',
            'code' => 'ABC123',
            'email' => 'john@example.com',
        ]);

        $this->assertEquals([
            'name' => 'John Doe',
            'code' => 'ABC123',
            'email' => 'john@example.com',
        ], $result);
    }

    public function testPreparePostHandlesEmptyStrings(): void
    {
        $result = $this->arr->preparePost([
            'name' => '',
            'email' => '  ',
        ]);

        $this->assertEquals([
            'name' => '',
            'email' => '',
        ], $result);
    }

    public function testPreparePostMixedTypes(): void
    {
        $result = $this->arr->preparePost([
            'name' => '  John  ',
            'age' => '30',
            'price' => '19.99',
            'active' => 'true',
            'bio' => 'Developer',
        ]);

        $this->assertEquals([
            'name' => 'John',
            'age' => 30,
            'price' => 19.99,
            'active' => 1,
            'bio' => 'Developer',
        ], $result);
    }

    public function testPreparePostWithZero(): void
    {
        $result = $this->arr->preparePost([
            'count' => '0',
        ]);

        $this->assertEquals([
            'count' => 0,
        ], $result);
    }

    public function testPreparePostNegativeNumbers(): void
    {
        $result = $this->arr->preparePost([
            'temp' => '-10',
            'balance' => '-99.99',
        ]);

        $this->assertEquals([
            'temp' => -10,
            'balance' => -99.99,
        ], $result);
    }

    public function testPreparePostDeeplyNested(): void
    {
        $result = $this->arr->preparePost([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'name' => '  value  ',
                        'count' => '5',
                    ],
                ],
            ],
        ]);

        $this->assertEquals([
            'level1' => [
                'level2' => [
                    'level3' => [
                        'name' => 'value',
                        'count' => 5,
                    ],
                ],
            ],
        ], $result);
    }

    public function testPreparePostEmptyArray(): void
    {
        $result = $this->arr->preparePost([]);

        $this->assertEquals([], $result);
    }

    public function testPreparePostWithMixedNumericAndNonNumeric(): void
    {
        $result = $this->arr->preparePost([
            'id' => '123',
            'code' => '123abc',
            'price' => '10.50',
            'label' => '10.50extra',
        ]);

        $this->assertEquals([
            'id' => 123,
            'code' => '123abc',
            'price' => 10.5,
            'label' => '10.50extra',
        ], $result);
    }
}
