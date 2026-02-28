<?php

declare(strict_types=1);

namespace App\Core;

use App\Middlewares\AuthMiddleware;
use App\Middlewares\RoleGuardMiddleware;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('GET', $path, $handler, $middlewares);
    }

    public function post(string $path, array $handler, array $middlewares = []): void
    {
        $this->add('POST', $path, $handler, $middlewares);
    }

    private function add(string $method, string $path, array $handler, array $middlewares): void
    {
        $this->routes[$method][$path] = ['handler' => $handler, 'middlewares' => $middlewares];
    }

    public function dispatch(string $method, string $path): void
    {
        $route = $this->routes[$method][$path] ?? null;

        if (!$route) {
            http_response_code(404);
            echo '404 - Ruta no encontrada';
            return;
        }

        foreach ($route['middlewares'] as $middleware) {
            if ($middleware === 'auth') {
                (new AuthMiddleware())->handle();
                continue;
            }

            if (str_starts_with($middleware, 'role:')) {
                $role = explode(':', $middleware, 2)[1] ?? '';
                (new RoleGuardMiddleware())->handle($role);
            }
        }

        [$class, $action] = $route['handler'];
        $controller = new $class();
        $controller->{$action}();
    }
}
