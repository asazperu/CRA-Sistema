<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use PDO;
use Throwable;

final class InstallController extends Controller
{
    public function index(): void
    {
        if (is_file(base_path('install.lock'))) {
            exit('Instalación bloqueada. Elimine install.lock solo si necesita reinstalar.');
        }

        view('install/index', ['title' => 'Instalador'], 'layouts/install');
    }

    public function store(): void
    {
        if (is_file(base_path('install.lock'))) {
            exit('Instalación ya completada.');
        }

        verify_csrf();

        $dbHost = sanitize_input((string) ($_POST['db_host'] ?? 'localhost'));
        $dbPort = sanitize_input((string) ($_POST['db_port'] ?? '3306'));
        $dbName = sanitize_input((string) ($_POST['db_name'] ?? ''));
        $dbUser = sanitize_input((string) ($_POST['db_user'] ?? ''));
        $dbPass = (string) ($_POST['db_pass'] ?? '');
        $appUrl = rtrim(sanitize_input((string) ($_POST['app_url'] ?? '')), '/');
        $adminName = sanitize_input((string) ($_POST['admin_name'] ?? 'Administrador'));
        $adminEmail = sanitize_input((string) ($_POST['admin_email'] ?? ''));
        $adminPass = (string) ($_POST['admin_pass'] ?? '');

        if (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPass) < 8 || !filter_var($appUrl, FILTER_VALIDATE_URL)) {
            flash('error', 'Validación fallida. Verifique URL, email y contraseña (mínimo 8).');
            redirect('/install');
        }

        try {
            $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $sql = file_get_contents(base_path('database.sql'));
            if ($sql === false) {
                throw new \RuntimeException('No se pudo leer database.sql');
            }

            $pdo->exec($sql);

            $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,status,created_at) VALUES (:name,:email,:password_hash,:role,:status,NOW())');
            $stmt->execute([
                'name' => $adminName,
                'email' => $adminEmail,
                'password_hash' => password_hash($adminPass, PASSWORD_DEFAULT),
                'role' => 'ADMIN',
                'status' => 'active',
            ]);

            $env = "APP_NAME=Castro Romero Abogados\nAPP_URL={$appUrl}\nAPP_ENV=production\nDB_HOST={$dbHost}\nDB_PORT={$dbPort}\nDB_NAME={$dbName}\nDB_USER={$dbUser}\nDB_PASS={$dbPass}\n";
            file_put_contents(base_path('.env'), $env);
            file_put_contents(base_path('install.lock'), 'installed:' . date('c'));

            echo 'Instalación completada. <a href="/login">Ir al login</a>';
        } catch (Throwable $e) {
            flash('error', 'Error de instalación: ' . $e->getMessage());
            redirect('/install');
        }
    }
}
