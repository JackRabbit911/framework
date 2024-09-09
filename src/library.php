<?php

use Sys\I18n\I18n;
// use Az\Session\Session;
use Az\Validation\Csrf;
use Az\Route\RouteCollectionInterface;
use Az\Session\SessionInterface;
use Dotenv\Dotenv;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Yaml\Yaml;
use Sys\App;
use Sys\Config\Config;
use Sys\SimpleRequest;
use Sys\Template\Template;
use HttpSoft\Emitter\SapiEmitter;
use Sys\Exception\ExceptionResponseFactory;
use Sys\Exception\MimeNegotiator;
use Sys\Helper\ResponseType;

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

function env(string $key, $default = null)
{
    static $entries;

    if (!$entries) {
        $entries = (Dotenv::createImmutable(ENVPATH))->load();
    }

    if (isset($entries[$key])) {
        $entry = trim($entries[$key]);
    } else {
        $entry = $default;
    }

    $entry = match ($entry) {
        'on', 'yes', 'true' => true,
        'no', 'off', 'false' => false,
        'null' => null,
        default => $entry,
    };

    if (is_string($entry) && preg_match('/\{(.+?)\}/', $entry, $matches)) {
        $entry = $matches[1];
        $dc = get_defined_constants(true)['user'];
        $entry = $dc[$entry];
    }

    return $entry;
}

function container()
{
    global $container;
    return $container;
}

function config(string $file, ?string $path = null, $default = null, $cache = null)
{
    $config = container()->get(Config::class);
    $is_cache = $config->getEnabled();

    if (isset($cache)) {
        $config->enable($cache);
    }
    
    $result = $config->get($file, $path, $default);
    $config->enable($is_cache);
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

function getMode(?string $path = null)
{
    static $mode;

    if (isset($mode)) {
        return $mode;
    }

    if (PHP_SAPI === 'cli') {
        $mode = 'cli';
        return $mode;
    }

    $arrMode = ($path) ? require $path : config('mode', null, []);

    foreach ($arrMode as $key => $paths) {
        foreach ($paths as $path) {
            if (strpos($_SERVER['REQUEST_URI'], $path . '/') === 0) {
                $mode = $key;
                return $mode;
            }
        }
    }

    $mode = 'web';
    return $mode;
}

function __(string $string, ?array $values = null): string
{
    $i18n = container()->get(I18n::class);
    return $i18n->gettext($string, $values);
}

function path($routeName, $params = [])
{
    $container = container();
    $routeCollection = $container->get(RouteCollectionInterface::class);
    $route = $routeCollection->getRoute($routeName);

    if (!array_key_exists('lang', $params) && $container->has(I18n::class)) {
        $i18n = $container->get(I18n::class);
        $params['lang'] = rtrim($i18n->langSegment(), '/');
    }

    return $route->path($params);
}

function url($routeName = null, $params = [])
{
    $request = container()->get(ServerRequestInterface::class);
    $scheme = getScheme($request);
    $host = $request->getServerParams()['SERVER_NAME'];

    $path = ($routeName) ? path($routeName, $params) : $request->getServerParams()['REQUEST_URI'];

    return $scheme . '://' . $host . $path;
}

function findPath($path, $all = false)
{
    // $paths = [CONFIG];
    $paths = glob(APPPATH . '*{\/src,}/' . ltrim($path, '/'), GLOB_BRACE);

    foreach ($paths as $path) {
        if (file_exists($path)) {
            if ($all) {
                $result[] = $path;
            } else {
                return $path;
            }
        }

        return $result ?? null;
    }
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

function getScheme($request)
{
    $serverParams = $request->getServerParams();

    if (isset($serverParams['HTTPS'])) {
        $scheme = $serverParams['HTTPS'];
    } else {
        $scheme = '';
    }

    if (($scheme) && ($scheme != 'off')) {
        return'https';
    }
    else {
        return 'http';
    }
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

function view(string $view, array $params = []): string
{
    static $tpl;

    if (!$tpl) {
        $tpl = container()->get(Template::class);
    }

    return $tpl->render($view, $params);
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
