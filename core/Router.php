<?php

namespace Core;

class Router
{
    private array $routes = [];
    private array $middlewares = [];

    public function get(string $path, $callback, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $callback, $middleware);
    }

    public function post(string $path, $callback, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $callback, $middleware);
    }

    private function addRoute(string $method, string $path, $callback, array $middleware): self
    {
        $this->routes[] = [
            'method'     => $method,
            'path'       => $path,
            'callback'   => $callback,
            'middleware'  => $middleware,
        ];
        return $this;
    }

    public function registerMiddleware(string $name, callable $handler): self
    {
        $this->middlewares[$name] = $handler;
        return $this;
    }

    public function dispatch(string $method, string $uri): mixed
    {
        $uri = parse_url($uri, PHP_URL_PATH);
        $uri = rtrim($uri, '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== strtoupper($method)) {
                continue;
            }

            $params = $this->matchPath($route['path'], $uri);
            if ($params === false) {
                continue;
            }

            foreach ($route['middleware'] as $mw) {
                if (!isset($this->middlewares[$mw])) {
                    throw new \RuntimeException("Middleware '{$mw}' is not registered.");
                }
                $result = call_user_func($this->middlewares[$mw]);
                if ($result === false) {
                    return null;
                }
            }

            return $this->executeCallback($route['callback'], $params);
        }

        http_response_code(404);
        echo '404 Not Found';
        return null;
    }

    private function matchPath(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', trim($routePath, '/'));
        $uriParts   = explode('/', trim($uri, '/'));

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        foreach ($routeParts as $i => $part) {
            if (preg_match('/^\{(\w+)\}$/', $part, $m)) {
                $params[$m[1]] = urldecode($uriParts[$i]);
            } elseif ($part !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function executeCallback($callback, array $params): mixed
    {
        // Handle 'Controller@method' string format
        if (is_string($callback) && str_contains($callback, '@')) {
            [$class, $method] = explode('@', $callback, 2);
            $instance = new $class();
            return call_user_func_array([$instance, $method], $params);
        }

        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_array($callback) && count($callback) === 2) {
            [$class, $method] = $callback;
            if (is_string($class)) {
                $class = new $class();
            }
            return call_user_func_array([$class, $method], $params);
        }

        throw new \RuntimeException('Invalid route callback.');
    }
}
