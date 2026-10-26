<?php

declare(strict_types=1);

namespace Tests\Az;

use Az\Route\Router;
use Az\Route\Route;
use PHPUnit\Framework\TestCase;
use HttpSoft\Response\TextResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $routes = [
            'home' => ['/', fn() => new TextResponse('Hello')],
            'auth' => ['/users/{id?}', fn() => new TextResponse('Users')],
        ];

        $this->router = new Router();
        $this->router->routes($routes);
    }

    public function testMatch()
    {
        $request = $this->mockRequest('/users');
        $match = $this->router->match($request);
        $this->assertInstanceOf(Route::class, $match);

        $request = $this->mockRequest('/users/5');
        $match = $this->router->match($request);
        $this->assertInstanceOf(Route::class, $match);
    }

    public function testNotMatch()
    {
        $request = $this->mockRequest('/blabla');
        $match = $this->router->match($request);
        $this->assertFalse($match);
    }

    public function testPath()
    {
        $this->assertSame('/users', $this->router->path('auth'));
        $this->assertSame('/users/5', $this->router->path('auth', ['id' => 5]));
        $this->assertSame('/', $this->router->path('home'));
    }

    private function mockRequest(string $path)
    {
        $uri = $this->createStub(UriInterface::class);
        $uri->method('getPath')
            ->willReturn($path);

        $request = $this->createStub(ServerRequestInterface::class);
        $request->method('getUri')
            ->willReturn($uri);
        $request->method('getMethod')
            ->willReturn('GET');

        return $request;
    }
}
