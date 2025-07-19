<?php

namespace Tests\Sys\Pipeline;

use Sys\Pipeline\PostProcess;
use PHPUnit\Framework\TestCase;
use HttpSoft\Message\Response;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Sys\Config\Config;
use Sys\Pipeline\PostProccessHandlerInterface;
use Sys\Pipeline\PostProcessInterface;

class PostProcessTest extends TestCase
{
    public function testPostProcess()
    {
        define('DOCROOT', './public/');
        require_once './application/common/config/bootstrap.php';

        $config = $this->createStub(Config::class);
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')->willReturn($config);

        $cut = new PostProcess($container);
        $response = new Response(200);

        $handler1 = $this->getHandler('Foo');
        $handler2 = $this->getHandler('Bar');

        $handler1 = $cut->enqueue($handler1);
        $handler2 = $cut->enqueue($handler2);

        $response = $cut->process($response);

        $this->assertInstanceOf(PostProcessInterface::class, $handler1);
        $this->assertTrue($response->hasHeader('X-Postprocess'));
        $this->assertSame('Foo,Bar', $response->getHeaderLine('X-Postprocess'));
    }

    private function getHandler(string $headerValue): object
    {
        return new class ($headerValue) implements PostProccessHandlerInterface
        {
            public function __construct(private string $headerValue){}

            public function handle(ResponseInterface $response): ResponseInterface
            {
                return $response->withAddedHeader('X-Postprocess', $this->headerValue);
            }
        };
    }
}
