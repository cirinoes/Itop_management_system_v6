<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];
    private array $middlewareMap = [
        'auth' => \App\Middleware\AuthMiddleware::class,
        'admin' => \App\Middleware\AdminMiddleware::class,
    ];

    public function get(string $path, array $handler): self
    {
        return $this->add('GET', $path, $handler);
    }

    public function post(string $path, array $handler): self
    {
        return $this->add('POST', $path, $handler);
    }

    private function add(string $method, string $path, array $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'handler' => $handler,
            'middleware' => null
        ];
        return $this;
    }

    public function middleware(string $key): self
    {
        $this->routes[array_key_last($this->routes)]['middleware'] = $key;
        return $this;
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_QUERY) ?? '';
        parse_str($path, $query);
        $page = $query['page'] ?? 'home';

        // For this basic router, we will map $page to the path.
        // e.g. /?page=dashboard means path = 'dashboard'
        
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $route['path'] === $page) {
                
                // Execute Middleware if exists
                if ($route['middleware'] && array_key_exists($route['middleware'], $this->middlewareMap)) {
                    $middlewareClass = $this->middlewareMap[$route['middleware']];
                    (new $middlewareClass())->handle();
                }

                // Execute Controller
                [$class, $action] = $route['handler'];
                if (class_exists($class) && method_exists($class, $action)) {
                    (new $class())->$action();
                    return;
                }
            }
        }

        $this->abort(404, 'Page not found.');
    }

    private function abort(int $code, string $message): void
    {
        http_response_code($code);
        echo $message;
        exit;
    }
}
