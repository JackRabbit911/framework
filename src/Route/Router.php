<?php declare(strict_types=1);

namespace Az\Route;

use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    private Matcher $matcher;
    private RouteFactory $factory;
    private array $routes = [];
    public ?string $allowedMethods = null;

    public function __construct(array|string|null $paths = null)
    {
        if ($paths && is_string($paths)) {
            $paths = [$paths];
        }

        if (!empty($paths)) {
            $this->setPaths($paths);
        }
        
        $this->matcher = new Matcher;
        $this->factory = new RouteFactory;
    }

    public function routes(array $routes): self
    {
        $this->routes = array_merge($this->routes, $routes);
        return $this;
    }

    public function setPaths($paths): self
    {
        foreach ($paths as $file) {
            if (is_file($file)) {
                $this->routes(require $file);
            }
        }

        return $this;
    }

    public function path(string $name, array $params = []): string
    {
        $pattern = $this->routes[$name][0];
        return $this->matcher->path($name, $pattern, $params);
    }

    public function match(ServerRequestInterface $request): mixed
    {
        $path = $request->getUri()->getPath();

        foreach ($this->routes as $name => $item) {
            [$pattern, $handler] = $item;
            $tokens = $item[2] ?? [];

            $params = $this->matcher->match($pattern, $path, $tokens);

            if ($params !== false) {
                $handler = $this->factory->handler($handler, $params);
                $reflect = $this->factory->reflect($handler);
                
                if (!$reflect) {
                    continue;
                }
                
                $route = $this->factory->create($handler, $tokens, $params);
                $route = $this->check($route, $request);

                if (!$route) {
                    continue;
                }
                
                return $route;
            }
        }

        return false;
    }

    private function check($route, $request)
    {
        $methods = $route->getMethods();
        $host = $route->getHost();
        $filters = $route->getFilters();
        $tokens = $route->getTokens();

        if (!empty($tokens)) {
            $parameters = $route->getParameters();

            foreach ($tokens as $key => $pattern) {
                if (!preg_match('~^(' . $pattern . ')$~i', $parameters[$key] ?? '')) {
                    return false;
                }
            }
        }

        if (!empty($methods) && !in_array($request->getMethod(), $methods, true)) {
            $this->allowedMethods = implode(', ', array_unique(array_filter($methods)));
            return false;
        }

        if ($host && !preg_match('~^' 
                . str_replace('.', '\\.', $host) 
                . '$~i', $request->getUri()->getHost())) {
            return false;
        }

        if ($route->isAjax() !== null && $route->isAjax() !== $this->is_ajax($request)) {
            return false;
        }

        foreach ($filters as $filter) {
            if (!$filter($route, $request)) {
                return false;
            }
        }

        return $route;
    }

    private function is_ajax(ServerRequestInterface $request)
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
}
