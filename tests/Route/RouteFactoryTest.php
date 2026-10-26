<?php

declare(strict_types=1);

namespace Tests\Az;

use Az\Route\RouteFactory;
use Az\Route\Route;
use PHPUnit\Framework\TestCase;
use HttpSoft\Response\TextResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use ReflectionClass;
use Error;

class Controller
{
    public function index(){}
}

final class RouteFactoryTest extends TestCase
{
    private RouteFactory $factory;

    protected function setUp(): void
    {
        $routes = [
            'home' => ['/', fn() => new TextResponse('Hello')],
            'auth' => ['/users/{id?}', fn() => new TextResponse('Users')],
        ];

        $this->factory = new RouteFactory();
    }

    public function testCreate()
    {
        $route = $this->factory->create([Controller::class, 'index'], [], []);
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testHandler()
    {
        $handler = $this->factory->handler(Controller::class, ['action' => 'index']);
        $this->assertSame([Controller::class, 'index'], $handler);
        $this->expectException(Error::class);
        $this->factory->handler('NotExistsClass', ['action' => 'foo']);
    }

    public function testReflect()
    {
        $reflect = $this->factory->reflect([Controller::class, 'index']);
        $this->assertInstanceOf(ReflectionClass::class, $reflect['class']);
    }
}
