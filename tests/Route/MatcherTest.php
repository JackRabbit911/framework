<?php

declare(strict_types=1);

namespace Tests\Az;

use Az\Route\Matcher;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

final class MatcherTest extends TestCase
{
    private Matcher $matcher;

    protected function setUp(): void
    {
        $this->matcher = new Matcher();
    }

    public function testMatch()
    {
        $match = $this->matcher->match('/users/{id}', '/users/5', ['id' => '\d*']);
        $this->assertEquals(['id' => '5'], $match);
    }

    public function testNotMatch()
    {
        $match = $this->matcher->match('/users/{id}', '/users/5', ['id' => '\D*']);
        $this->assertFalse($match);

        $match = $this->matcher->match('/users/{id}', '/users', []);
        $this->assertFalse($match);
    }

    public function testPath()
    {
        $path = $this->matcher->path('users', '/users/{id}', ['id' => 5]);
        $this->assertSame('/users/5', $path);

        $this->expectException(InvalidArgumentException::class);
        $path = $this->matcher->path('users', '/users/{id}', []);
    }
}
