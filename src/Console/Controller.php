<?php

namespace Sys\Console;

use HttpSoft\Response\JsonResponse;
use Psr\Container\ContainerInterface;
use Sys\Controller\BaseController;
use Az\Route\Route;

#[Route(methods: ['post', 'get'])]
#[Route(filter: 'Sys\Console\filter')]
final class Controller extends BaseController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke($model, $method)
    {
        $model = str_replace('/', '\\', $model);
        $args = $this->request->getQueryParams();

        $instance = $this->container->make($model, $args);

        $body = $this->request->getBody()->getContents();       
        $body = json_decode($body, true);

        $data = $this->container->call([$instance, $method], $body);

        return new JsonResponse($data);
    }
}

function filter($route)
{
    $params = $route->getParameters();
    $model = str_replace('/', '\\', $params['model']);

    if (method_exists($model, $params['method'])) {
        return true;
    }

    return false;
}
