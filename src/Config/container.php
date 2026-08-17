<?php

use Auth\User;
use Az\Route\Matcher;
use Az\Route\RouteFactory;
use HttpSoft\ServerRequest\ServerRequestCreator;
use HttpSoft\Emitter\EmitterInterface;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Runner\MiddlewareResolverInterface;
use HttpSoft\Runner\MiddlewareResolver;
use HttpSoft\Message\RequestFactory;
use HttpSoft\Message\ServerRequestFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Http\Client\ClientInterface as PsrHttpClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Az\Route\Router;
use Az\Route\RouterInterface;
use Sys\Exception\SetErrorHandlerInterface;
use Sys\Exception\WhoopsAdapter;
use Sys\DefaultHandler;
use Sys\Exception\ExceptionResponseFactory;
use Pecee\Pixie\Connection;
use Pecee\Pixie\QueryBuilder\IQueryBuilderHandler;
use Az\Session\Session;
use Az\Session\Driver;
use Az\Session\SessionInterface;
use Az\Validation\Response;
use Az\Validation\Validation;
use Az\Validation\ValidationResponseInterface;
use GuzzleHttp\Client as GuzzleClient;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Sys\Contract\UserInterface;
use Sys\Template\TemplateFactory;
use Sys\Template\TemplateInterface;
use Sys\I18n\Model\File as I18nModelFile;
use Sys\I18n\Model\I18nModelInterface;
use Sys\Pipeline\Pipeline;
use Sys\Pipeline\PipelineInterface;
use Sys\Pipeline\PostProcess;
use Sys\Pipeline\PostProcessInterface;

return [
    ServerRequestInterface::class => fn() => (new ServerRequestCreator())->create(),
    RequestHandlerInterface::class => fn(ExceptionResponseFactory $factory) => new DefaultHandler($factory),

    RequestFactoryInterface::class => fn() => new RequestFactory,
    ServerRequestFactoryInterface::class => fn() => new ServerRequestFactory,
    PsrHttpClientInterface::class => DI\create(GuzzleClient::class),

    RouterInterface::class => fn() => new Router(new Matcher, new RouteFactory, 'getRoutePaths'),
    PipelineInterface::class => fn(ContainerInterface $c) => new Pipeline($c),
    EmitterInterface::class => fn() => new SapiEmitter,
    LoggerInterface::class => function () {
        $logger = new Logger('e');
        $logger->setTimezone(new \DateTimeZone(env('APP_TZ')));
        $logger->pushHandler(new StreamHandler(STORAGE . 'logs/error.log', Level::Error, true, 0777));
        return $logger;
    },
    'logger' => function ($name, $file, $level) {
        $logger = new Logger($name);
        $logger->setTimezone(new \DateTimeZone(env('APP_TZ')));
        $logger->pushHandler(new StreamHandler(STORAGE . 'logs/' . $file, $level, true, 0777));
        return $logger;
    },
    SetErrorHandlerInterface::class => fn(
        LoggerInterface $logger, 
        EmitterInterface $emitter, 
        ExceptionResponseFactory $response_factory) 
        => new WhoopsAdapter($logger, $emitter, $response_factory),
    
    PostProcessInterface::class => fn(ContainerInterface $c) => new PostProcess($c),
    IQueryBuilderHandler::class => fn()
        => (new Connection('mysql', config('database', 'connect.mysql')))->getQueryBuilder(),
    
    SessionInterface::class => function (ContainerInterface $c) {
        switch (env('SESSION_DRIVER')) {
            case 'DB':
                $qb = $c->get(IQueryBuilderHandler::class);
                $handler = new Driver\Db($qb->pdo());
                break;
            default:
                $handler = null;
        }

        return new Session(config('session'), $handler);
    },

    TemplateInterface::class => fn() => (new TemplateFactory())->create(),
    I18nModelInterface::class => fn() => new I18nModelFile(findPaths('i18n')),
    ValidationResponseInterface::class => fn(ContainerInterface $c) => $c->get(Response::class),
    MiddlewareResolverInterface::class => fn(ContainerInterface $c) => new MiddlewareResolver($c),

    Validation::class => fn(ContainerInterface $c)
        => new Validation($c->get(ValidationResponseInterface::class), config('validation')),

    UserInterface::class => User::class,
];
