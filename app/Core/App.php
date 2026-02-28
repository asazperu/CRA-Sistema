<?php

declare(strict_types=1);

namespace App\Core;

use App\Controllers\AuthController;
use App\Controllers\ChatController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\EventController;
use App\Controllers\InstallController;

final class App
{
    private static string $basePath;

    public static function boot(string $basePath, bool $installMode = false): void
    {
        self::$basePath = rtrim($basePath, '/');

        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            ini_set('session.cookie_samesite', 'Lax');
            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
                ini_set('session.cookie_secure', '1');
            }
            session_start();
            if (!isset($_SESSION['_initiated'])) {
                session_regenerate_id(true);
                $_SESSION['_initiated'] = true;
            }
        }

        Config::load(self::$basePath);
        date_default_timezone_set(config('app.timezone', 'America/Lima'));

        if (!$installMode && !is_file(self::$basePath . '/install.lock')) {
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            if (!in_array($path, ['/install', '/install.php'], true)) {
                redirect('/install');
            }
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

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        $router->get('/', [DashboardController::class, 'index'], ['auth']);

        $router->get('/login', [AuthController::class, 'showLogin']);
        $router->post('/login', [AuthController::class, 'login']);
        $router->post('/logout', [AuthController::class, 'logout'], ['auth']);
        $router->get('/password/change', [AuthController::class, 'showChangePassword'], ['auth']);
        $router->post('/password/change', [AuthController::class, 'changePassword'], ['auth']);

        $router->get('/chat', [ChatController::class, 'index'], ['auth']);
        $router->post('/chat/new', [ChatController::class, 'create'], ['auth']);
        $router->post('/chat/message', [ChatController::class, 'storeMessage'], ['auth']);
        $router->post('/chat/rename', [ChatController::class, 'rename'], ['auth']);
        $router->post('/chat/delete', [ChatController::class, 'delete'], ['auth']);
        $router->post('/chat/event/create', [ChatController::class, 'createEvent'], ['auth']);

        $router->get('/documentos', [DocumentController::class, 'index'], ['auth']);
        $router->post('/documentos/upload', [DocumentController::class, 'upload'], ['auth']);
        $router->get('/documentos/download', [DocumentController::class, 'download'], ['auth']);
        $router->post('/documentos/delete', [DocumentController::class, 'delete'], ['auth']);
        $router->post('/documentos/reprocess', [DocumentController::class, 'reprocess'], ['auth']);

        $router->get('/eventos', [EventController::class, 'index'], ['auth']);
        $router->post('/eventos/create', [EventController::class, 'create'], ['auth']);
        $router->get('/eventos/ics', [EventController::class, 'downloadIcs'], ['auth']);

        $router->get('/admin', [DashboardController::class, 'admin'], ['auth', 'role:ADMIN']);
        $router->post('/admin/brand', [DashboardController::class, 'saveBrand'], ['auth', 'role:ADMIN']);
        $router->post('/admin/ai', [DashboardController::class, 'saveAI'], ['auth', 'role:ADMIN']);
        $router->post('/admin/kb/create', [DashboardController::class, 'createKbArticle'], ['auth', 'role:ADMIN']);
        $router->post('/admin/users/create', [DashboardController::class, 'createUser'], ['auth', 'role:ADMIN']);
        $router->post('/admin/users/reset-password', [DashboardController::class, 'resetUserPassword'], ['auth', 'role:ADMIN']);
        $router->post('/admin/users/toggle-status', [DashboardController::class, 'toggleUserStatus'], ['auth', 'role:ADMIN']);

        $router->get('/install', [InstallController::class, 'index']);
        $router->post('/install', [InstallController::class, 'store']);
        $router->post('/install/test-connection', [InstallController::class, 'testConnection']);

        $router->dispatch($method, $path);

        $_SESSION['_old'] = [];
        $_SESSION['_flash'] = [];
    }
}
