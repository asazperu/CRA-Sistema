<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\ChatController;
use App\Controllers\DashboardController;
use App\Controllers\InstallController;

final class App
{
    private static string $basePath;

    public static function boot(string $basePath, bool $installMode = false): void
    {
        self::$basePath = rtrim($basePath, '/');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        Config::load(self::$basePath);
        date_default_timezone_set(config('app.timezone', 'America/Lima'));

        if (!$installMode && !is_file(self::$basePath . '/install.lock') && !str_contains($_SERVER['REQUEST_URI'] ?? '', 'install.php')) {
            redirect('/install.php');
        }
    }

    public static function basePath(): string
    {
        return self::$basePath;
    }

    public static function run(): void
    {
        $router = new Router();

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        if (str_contains($path, '/install.php')) {
            $path = str_replace('/install.php', '', $path) ?: '/';
        }

        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $router->get('/', [DashboardController::class, 'index'], true);
        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->post('/logout', [AuthController::class, 'logout'], true);

        $router->get('/chat', [ChatController::class, 'index'], true);
        $router->post('/chat/new', [ChatController::class, 'create'], true);
        $router->get('/chat/view', [ChatController::class, 'show'], true);
        $router->post('/chat/message', [ChatController::class, 'storeMessage'], true);

        $router->get('/install', [InstallController::class, 'index']);
        $router->post('/install', [InstallController::class, 'store']);

        $router->dispatch($method, $path);

        $_SESSION['_old'] = [];
        $_SESSION['_flash'] = [];
    }
}
