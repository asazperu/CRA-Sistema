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

            $defaultSystemPrompt = "Eres un asistente legal para abogados peruanos.\n"
                . "Responde únicamente sobre derecho peruano.\n"
                . "Formato obligatorio:\n"
                . "1) Resumen ejecutivo\n"
                . "2) Base normativa\n"
                . "3) Jurisprudencia/Criterios\n"
                . "4) Aplicación al caso\n"
                . "5) Riesgos\n"
                . "6) Recomendaciones\n"
                . "7) Checklist\n"
                . "No inventes normas, artículos, sentencias o fuentes. Si falta información o no puedes verificar una cita, indícalo expresamente.\n"
                . "Incluye siempre un disclaimer final indicando que es orientación general y no sustituye asesoría legal profesional.";

            $settingsStmt = $pdo->prepare('INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (:setting_key, :setting_value, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)');
            $defaultSettings = [
                'openrouter_system_prompt' => $defaultSystemPrompt,
                'brand_name' => 'Castro Romero Abogados',
                'brand_logo' => '',
                'brand_color_primary' => '#4f7cff',
                'brand_color_secondary' => '#1f2a50',
                'ai_model' => 'openai/gpt-4o-mini',
                'ai_temperature' => '0.2',
                'ai_max_tokens' => '1200',
            ];
            foreach ($defaultSettings as $k => $v) {
                $settingsStmt->execute([
                    'setting_key' => $k,
                    'setting_value' => $v,
                ]);
            }

            $env = "APP_NAME=Castro Romero Abogados\nAPP_URL={$input['app_url']}\nAPP_ENV=production\nDB_HOST={$input['db_host']}\nDB_PORT={$input['db_port']}\nDB_NAME={$input['db_name']}\nDB_USER={$input['db_user']}\nDB_PASS={$input['db_pass']}\nOPENROUTER_API_KEY=\nOPENROUTER_MODEL=openai/gpt-4o-mini\n";
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
