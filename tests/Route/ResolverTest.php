<?php

declare(strict_types=1);

namespace Tests\Az;

use Az\Route\Resolver;
use PHPUnit\Framework\TestCase;
use HttpSoft\Response\TextResponse;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Container\ContainerInterface;

final class ResolverTest extends TestCase
{
    public function testResolve()
    {
        $container = $this->createStub(ContainerInterface::class);
        $resolver = new Resolver($container);
        $handler = fn() => new TextResponse('foo');
        $handler = $resolver->resolve($handler);
        $this->assertInstanceOf(RequestHandlerInterface::class, $handler);
    }
}
