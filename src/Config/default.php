<?php declare(strict_types=1);

use Az\Route\Middleware\RouteDispatch;
use Az\Route\Middleware\RouteMatch;
use Sys\I18n\I18nMiddleware;
use Sys\Middleware\ControllerAttribute;
use Sys\Service\Robots;
use Sys\Model\CommitListener;

return [
    'common' => [
        'migrations_dir' => APPPATH . 'common/migrations/',
        'create' => [
            'blanks' => APPPATH . 'common/create/blanks/',
        ],
    ],
    'database' => [
        'connect' => [
            'mysql' => [
                'host' => env('DB_HOST'),
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
                'charset' => env('DB_CHARSET'),
                'root_password' => env('DB_ROOT_PASSWORD'),
            ],
        ],
    ],
    'post_process' => [
            Robots::class,
            CommitListener::class,
    ],
    'session' => [
        'cookie' => [
            'lifetime' => env('SESSION_LIFETIME'),
            'path' => env('SESSION_PATH'),
            'domain' => env('SESSION_DOMAIN'),
            'secure' => env('SESSION_ENCRYPT'),
    
        ],
        'options' => [
            'gc_maxlifetime' => env('SESSION_LIFETIME'),
        ],
        // 'guard_agent' => true,
    ],
    'pipeline' => [
        RouteMatch::class,
        ControllerAttribute::class,
        RouteDispatch::class,
    ],
];
