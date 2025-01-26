<?php

namespace Sys\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class TemplateFactory
{
    private array $options = [
        'strict_variables' => ENV > PRODUCTION,
        'debug' => ENV > PRODUCTION,
    ];

    public function create($config = null): Template
    {
        if (!$config) {
            $config = config('template');
        }

        $viewPath = $config['view_path'] ?? glob(APPPATH . '**/views/', GLOB_ONLYDIR);
        $options = $config['options'] ?? $this->options;

        $loader = new FilesystemLoader($viewPath, APPPATH);
        $twig = new Environment($loader, $options);

        if (ENV > PRODUCTION) {
            $twig->addExtension(new \Twig\Extension\DebugExtension());
        }

        
        $twig->addFunction(new TwigFunction('path', function ($routeName, $params = []) {
            return path($routeName, $params);
        }));

        $twig->addFunction(new TwigFunction('url', function ($routeName, $params = []) {
            return url($routeName, $params);
        }));

        $twig->addFunction(new TwigFunction('json', function ($string, $unique = false) {
            return json($string, $unique);
        }));

        $twig->addFunction(new TwigFunction('csrf', function () {
            return '<input name="_csrf" type="hidden" value="' . createCsrf() . '">';
        }));

        $twig->addFunction(new TwigFunction('method', function ($method) {
            return '<input name="_method" type="hidden" value="' . $method . '">';
        }));

        $twig->addFunction(new TwigFunction('base64', function ($path) {
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }));

        return new Template($twig, 'twig');
    }
}
