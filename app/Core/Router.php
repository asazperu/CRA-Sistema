<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, bool $auth = false): void
    {
        $this->add('GET', $path, $handler, $auth);
    }

    public function post(string $path, array $handler, bool $auth = false): void
    {
        $this->add('POST', $path, $handler, $auth);
    }

    private function add(string $method, string $path, array $handler, bool $auth): void
    {
        $this->routes[$method][$path] = ['handler' => $handler, 'auth' => $auth];
    }

    public function dispatch(string $method, string $path): void
    {
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            http_response_code(404);
            echo '404 - Ruta no encontrada';
            return;
        }

        if ($route['auth'] && !Auth::check()) {
            redirect('/login');
        }

        [$class, $action] = $route['handler'];
        $controller = new $class();
        $controller->{$action}();
    }
}
