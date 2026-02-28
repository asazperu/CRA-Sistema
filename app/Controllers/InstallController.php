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

    public function testConnection(): void
    {
        if (is_file(base_path('install.lock'))) {
            exit('Instalación ya completada.');
        }

        verify_csrf();
        $input = $this->validatedInput();
        $_SESSION['_old'] = $input;

        try {
            new PDO(
                "mysql:host={$input['db_host']};port={$input['db_port']};dbname={$input['db_name']};charset=utf8mb4",
                $input['db_user'],
                $input['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            flash('success', 'Conexión MySQL exitosa. Ya puedes ejecutar la instalación.');
        } catch (Throwable $e) {
            flash('error', 'No se pudo conectar a MySQL: ' . $e->getMessage());
        }

        redirect('/install');
    }

    public function store(): void
    {
        if (is_file(base_path('install.lock'))) {
            exit('Instalación ya completada.');
        }

        verify_csrf();

        $input = $this->validatedInput();

        try {
            $pdo = new PDO(
                "mysql:host={$input['db_host']};port={$input['db_port']};dbname={$input['db_name']};charset=utf8mb4",
                $input['db_user'],
                $input['db_pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            $sql = file_get_contents(base_path('database.sql'));
            if ($sql === false) {
                throw new \RuntimeException('No se pudo leer database.sql');
            }
            $pdo->exec($sql);

            $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash,role,status,created_at) VALUES (:name,:email,:password_hash,:role,:status,NOW())');
            $stmt->execute([
                'name' => $input['admin_name'],
                'email' => $input['admin_email'],
                'password_hash' => password_hash($input['admin_pass'], PASSWORD_DEFAULT),
                'role' => 'ADMIN',
                'status' => 'active',
            ]);

            $env = "APP_NAME=Castro Romero Abogados\nAPP_URL={$input['app_url']}\nAPP_ENV=production\nDB_HOST={$input['db_host']}\nDB_PORT={$input['db_port']}\nDB_NAME={$input['db_name']}\nDB_USER={$input['db_user']}\nDB_PASS={$input['db_pass']}\n";
            file_put_contents(base_path('.env'), $env);
            file_put_contents(base_path('install.lock'), 'installed:' . date('c'));

            echo 'Instalación completada. <a href="/login">Ir al login</a>';
        } catch (Throwable $e) {
            flash('error', 'Error de instalación: ' . $e->getMessage());
            $_SESSION['_old'] = $input;
            redirect('/install');
        }
    }

    private function validatedInput(): array
    {
        $data = [
            'db_host' => sanitize_input((string) ($_POST['db_host'] ?? 'localhost')),
            'db_port' => sanitize_input((string) ($_POST['db_port'] ?? '3306')),
            'db_name' => sanitize_input((string) ($_POST['db_name'] ?? '')),
            'db_user' => sanitize_input((string) ($_POST['db_user'] ?? '')),
            'db_pass' => (string) ($_POST['db_pass'] ?? ''),
            'app_url' => rtrim(sanitize_input((string) ($_POST['app_url'] ?? '')), '/'),
            'admin_name' => sanitize_input((string) ($_POST['admin_name'] ?? 'Administrador')),
            'admin_email' => sanitize_input((string) ($_POST['admin_email'] ?? '')),
            'admin_pass' => (string) ($_POST['admin_pass'] ?? ''),
        ];

        if (!filter_var($data['admin_email'], FILTER_VALIDATE_EMAIL)
            || strlen($data['admin_pass']) < 8
            || !filter_var($data['app_url'], FILTER_VALIDATE_URL)
            || $data['db_name'] === ''
            || $data['db_user'] === '') {
            flash('error', 'Validación fallida. Verifique URL, email, contraseña (mínimo 8) y credenciales DB.');
            $_SESSION['_old'] = $data;
            redirect('/install');
        }

        return $data;
    }
}
