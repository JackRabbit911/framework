<?php

use Sys\I18n\I18n;
use Az\Route\RouterInterface;
use Az\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Sys\Config\Config;
use Sys\SimpleRequest;
use HttpSoft\Emitter\SapiEmitter;
use HttpSoft\Response\HtmlResponse;
use Nette\Utils\Finder;
use Psr\Http\Message\ResponseInterface;
use Sys\Exception\ExceptionResponseFactory;
use Sys\Helper\MimeNegotiator;
use Sys\Helper\ResponseType;
use Sys\Template\TemplateInterface;

function dd(...$values)
{
    ob_start();
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 1);
        echo 'file: ', $trace[0]['file'], ' line: ', $trace[0]['line'], '<br>';
        var_dump(...$values);
    $output = ob_get_clean();

    echo (php_sapi_name() !== 'cli') ? $output : str_replace('<br>', PHP_EOL, strip_tags($output, ['<br>', '<pre>']));
    exit;
}

function env(?string $key = null, $default = null)
{
    static $entries;

    if (!$entries) {
        $loader = new josegonzalez\Dotenv\Loader(ENVPATH . '.env');
        $entries = $loader->parse()->toArray();
    }

    $entry = (isset($entries[$key])) ? $entries[$key] : $default;

    if (is_string($entry)) {
        if (preg_match('/\{(.+?)\}/', $entry, $matches)) {
            $entry = $matches[1];
            $dc = get_defined_constants(true)['user'];
            $entry = $dc[$entry];
        }

        if (preg_match('/\[(.+?)\]/', $entry, $matches)) {
            $entry = $matches[1];
            $entry = explode(',', str_replace([' ', "'", '"'], '', $entry));
        }
    }

    return ($key) ? $entry : $entries;
}

function container()
{
    global $container;
    return $container;
}

function config(string $file, ?string $path = null, $default = null, $cache = null)
{
    $config = container()?->get(Config::class);
    $is_cache = $config?->getEnabled();

    if (isset($cache)) {
        $config->enable($cache);
    }
    
    $result = $config?->get($file, $path, $default);
    $config?->enable($is_cache);
    return $result;
}

function dot(&$arr, $path, $default = null, $separator = '.') {
    $keys = explode($separator, $path);

    foreach ($keys as $key) {
        if (!is_array($arr) || !array_key_exists($key, $arr)) {
            $arr = &$default;
        } else {
            $arr = &$arr[$key];
        }       
    }

    return $arr;
}

function __(string $string, ?array $values = null): string
{
    if (container()->has(I18n::class)) {
        try {
            $i18n = container()->get(I18n::class);
            return $i18n->gettext($string, $values);
        } catch (Exception $e) {
            return ($values) ? strtr($string, $values) : $string;
        }
    }

    return ($values) ? strtr($string, $values) : $string;
}

function path($routeName, $params = [])
{
    $container = container();
    $router = $container->get(RouterInterface::class);
    $path = $router->path($routeName, $params);

    if ($container->has(I18n::class)) {
        $i18n = $container->get(I18n::class);
        $path = $i18n->path($path);
    }

    $uriPrefix = $GLOBALS['URI_PREFIX'] ?? '';
    return $uriPrefix . $path;
}

function url($routeName = null, $params = [])
{
    $request = container()->get(ServerRequestInterface::class);
    $uri = $request->getUri();
    $scheme = $uri->getScheme();
    $host = $uri->getHost();
    $path = ($routeName) ? path($routeName, $params) : $uri->getPath();

    return $scheme . '://' . $host . $path;
}

// function findPath($path, $all = false)
// {
//     $paths = glob(APPPATH . '*{\/src,}/' . ltrim($path, '/'), GLOB_BRACE);

//     foreach ($paths as $path) {
//         if (file_exists($path)) {
//             if ($all) {
//                 $result[] = $path;
//             } else {
//                 return $path;
//             }
//         }
//     }

//     return $result ?? null;
// }

function findPaths(array|string $pattern)
{
    $iterator = Finder::findDirectories($pattern)
        ->from(APPPATH)
        ->sortByName();

    foreach ($iterator as $item) {
        $paths[] = $item->getPathname();
    }

    return $paths ?? [];
}

function json(?string $string, $unique = false)
{
    if (empty($string)) {
        return [];
    }

    $array = json_decode($string, true) ?? [];
    return ($unique) ? array_unique($array) : $array;
}

function createCsrf()
{
    $salt = $_SERVER['HTTP_USER_AGENT'] ?? uniqid();
    $token = md5($salt.time().bin2hex(random_bytes(12)));
    $session = container()->get(SessionInterface::class);
    $session->set('_csrf', $token);
    return $token;
}

function getCallable(string|array $callable): mixed
{
    if (is_string($callable)) {
        if (str_contains($callable, '::')) {
            $callable = explode('::', $callable);
        } elseif (str_contains($callable, '@')) {
            $callable = explode('@', $callable);
        } else {
            $callable = [$callable, '__invoke'];
        }
    }
    
    return $callable;
}

function call($callable, array $data = []) {
    $container = container();
    $callable = getCallable($callable);

    try {
        return $container->call($callable, $data);        
    } catch (\DI\Definition\Exception\InvalidDefinition|\Invoker\Exception\InvocationException $e) {
        [$class, $method] = $callable;
        $instance = (is_string($class)) ? $container->make($class, $data) : $class;
        return $container->call([$instance, $method], $data);
    }
}

function is_ajax(ServerRequestInterface $request)
{
    $key = 'x_requested_with';
    $header = $request->getHeaderLine($key);

    if (empty($header)) {
        $header = $request->getHeaderLine('http_' . $key);
    }

    if (empty($header)) {
        $header = $request->getHeaderLine(strtoupper($key));
    }

    if (empty($header)) {
        $header = $request->getHeaderLine(strtoupper('http_' . $key));
    }

    if (empty($header)) {
        return false;
    }

    return true;
}

function render($file, $data)
{
    extract($data, EXTR_SKIP);               
    ob_start();
    include $file;
    return ob_get_clean();
}

function request($psr = false)
{
    static $simple_request;

    if ($psr == false && $simple_request) {
        return $simple_request;
    }

    if (isset($GLOBALS['request'])) {
        $request = &$GLOBALS['request'];
    } else {
        $request = container()->get(ServerRequestInterface::class);
    }

    if ($psr === false) {
        $simple_request = new SimpleRequest($request);
        return $simple_request;
    }

    return $request;
}

function view(string $view, array $params = [], bool $is_response = false): string|ResponseInterface
{
    static $tpl;

    if (!$tpl) {
        $tpl = container()->get(TemplateInterface::class);
    }

    $str = $tpl->render($view, $params);
    return ($is_response) ? new HtmlResponse($str) : $str;
}

function abort($code = 404)
{
    $accept_header = request()->header('Accept');
    $mimeNegotiator = new MimeNegotiator($accept_header);
    $response_type = $mimeNegotiator->getResponseType();
    $response_type = ResponseType::from($response_type);
    $factory = container()->get(ExceptionResponseFactory::class);
    $response = $factory->createResponse($response_type, $code);
    container()->call([SapiEmitter::class, 'emit'], ['response' => $response]);
    exit;
}

function redirect($url, $code = 302)
{
    header('Location: ' . $url, true, $code);
    exit;
}

function logger(?string $content, string $file = 'log.txt'):void
{
    if ($content) {
        $prefix = STORAGE . 'logs/';
        $file = $prefix . $file;
        file_put_contents($file, $content, FILE_APPEND);
    }
}
