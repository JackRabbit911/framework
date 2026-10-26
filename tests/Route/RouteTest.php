<?php

declare(strict_types=1);

namespace Tests\Az;

use Az\Route\Route;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;

final class RouteTest extends TestCase
{
    private Route $route;

    protected function setUp(): void
    {
        $handler = [$this->handler(), 'index'];
        $tokens = ['id' => '\d*'];

        $this->route = new Route($handler, $tokens);
        $this->route->host('localhost')
            ->methods(['post', 'patch', 'delete'])
            ->filter(fn () => true)
            ->setParameters(['id' => 5]);
    }

    public function testGetTokens()
    {
        $tokens = $this->route->getTokens();
        $this->assertSame(['id' => '\d*'], $tokens);
    }

    public function testGetParameters()
    {
        $params = $this->route->getParameters();
        $this->assertSame(['id' => 5], $params);
    }

    public function testGetHandler()
    {
        $handler = $this->route->getHandler();
        $this->assertEquals([$this->handler(), 'index'], $handler);
    }

    public function testGetMethods()
    {
        $methods = $this->route->getMethods();
        $this->assertSame(['POST', 'PATCH', 'DELETE'], $methods);
    }

    public function testGetHost()
    {
        $host = $this->route->getHost();
        $this->assertSame('localhost', $host);
    }

    public function testGetFilters()
    {
        $filters = $this->route->getFilters();
        $this->assertContainsOnlyCallable($filters);
    }

    public function testGetInstance()
    {
        $instance = $this->route->getInstance();
        $this->assertInstanceOf(RequestHandlerInterface::class, $instance);
    }

    private function handler()
    {
        return new class () {
            public function index(){}
        };
    }
}
