<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\ApiUsageLog;
use App\Models\AuditLog;
use App\Models\KnowledgeArticle;
use App\Models\Setting;
use App\Models\User;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        view('dashboard/index', ['title' => 'Panel', 'user' => $user]);
    }

    public function admin(): void
    {
        $user = Auth::user();
        $settings = new Setting();
        $kbQuery = trim((string) ($_GET['kb_q'] ?? ''));

        $brand = [
            'name' => $settings->get('brand_name', 'Castro Romero Abogados'),
            'logo' => $settings->get('brand_logo', ''),
            'primary' => $settings->get('brand_color_primary', '#4f7cff'),
            'secondary' => $settings->get('brand_color_secondary', '#1f2a50'),
        ];

        $ai = [
            'model' => $settings->get('ai_model', config('env.OPENROUTER_MODEL', 'openai/gpt-4o-mini')),
            'temperature' => $settings->get('ai_temperature', '0.2'),
            'max_tokens' => $settings->get('ai_max_tokens', '1200'),
            'system_prompt' => $settings->get('openrouter_system_prompt', $this->defaultSystemPrompt()),
        ];

        $kbResults = (new KnowledgeArticle())->search($kbQuery, 40);

        view('dashboard/admin', [
            'title' => 'Panel Admin',
            'user' => $user,
            'brand' => $brand,
            'ai' => $ai,
            'users' => (new User())->all(),
            'auditLogs' => (new AuditLog())->latest(50),
            'usageLogs' => (new ApiUsageLog())->latest(50),
            'kbQuery' => $kbQuery,
            'kbResults' => $kbResults,
        ]);
    }

    public function saveBrand(): void
    {
        verify_csrf();
        $settings = new Setting();
        $settings->set('brand_name', sanitize_input((string) ($_POST['brand_name'] ?? 'Castro Romero Abogados')));
        $settings->set('brand_logo', sanitize_input((string) ($_POST['brand_logo'] ?? '')));
        $settings->set('brand_color_primary', sanitize_input((string) ($_POST['brand_color_primary'] ?? '#4f7cff')));
        $settings->set('brand_color_secondary', sanitize_input((string) ($_POST['brand_color_secondary'] ?? '#1f2a50')));
        $this->audit('update_brand', 'settings', null, ['brand_name' => $_POST['brand_name'] ?? '']);
        flash('success', 'Marca actualizada.');
        redirect('/admin');
    }

    public function saveAI(): void
    {
        verify_csrf();

        $model = sanitize_input((string) ($_POST['ai_model'] ?? 'openai/gpt-4o-mini'));
        $temperature = (string) ($_POST['ai_temperature'] ?? '0.2');
        $maxTokens = (string) ($_POST['ai_max_tokens'] ?? '1200');
        $prompt = trim((string) ($_POST['system_prompt'] ?? ''));

        if ($prompt === '') {
            flash('error', 'El system prompt no puede estar vacío.');
            redirect('/admin');
        }

        $settings = new Setting();
        $settings->set('ai_model', $model);
        $settings->set('ai_temperature', $temperature);
        $settings->set('ai_max_tokens', $maxTokens);
        $settings->set('openrouter_system_prompt', $prompt);
        $this->audit('update_ai_settings', 'settings', null, ['model' => $model]);
        flash('success', 'Configuración IA actualizada.');
        redirect('/admin');
    }

    public function createKbArticle(): void
    {
        verify_csrf();
        $user = Auth::user();

        $title = sanitize_input((string) ($_POST['title'] ?? ''));
        $body = trim((string) ($_POST['body'] ?? ''));
        $tags = sanitize_input((string) ($_POST['tags'] ?? ''));
        $source = sanitize_input((string) ($_POST['source_url'] ?? ''));

        if ($title === '' || $body === '') {
            flash('error', 'Título y texto legal son obligatorios para KB.');
            redirect('/admin');
        }

        $slug = $this->slugify($title) . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        $id = (new KnowledgeArticle())->create([
            'title' => $title,
            'slug' => $slug,
            'body' => $body,
            'tags' => $tags,
            'source_url' => $source,
            'created_by' => (int) ($user['id'] ?? 0),
            'is_published' => 1,
        ]);

        $this->audit('kb_create_article', 'knowledge_articles', $id, ['title' => $title, 'tags' => $tags]);
        flash('success', 'Documento legal cargado en KB.');
        redirect('/admin');
    }

    public function createUser(): void
    {
        verify_csrf();

        $name = sanitize_input((string) ($_POST['name'] ?? ''));
        $email = sanitize_input((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = sanitize_input((string) ($_POST['role'] ?? 'USER'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8 || $name === '') {
            flash('error', 'Datos de usuario inválidos.');
            redirect('/admin');
        }

        $id = (new User())->createUser($name, $email, $password, $role === 'ADMIN' ? 'ADMIN' : 'USER');
        $this->audit('create_user', 'users', $id, ['email' => $email, 'role' => $role]);
        flash('success', 'Usuario creado.');
        redirect('/admin');
    }

    public function resetUserPassword(): void
    {
        verify_csrf();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $newPassword = (string) ($_POST['new_password'] ?? '');
        if ($userId <= 0 || strlen($newPassword) < 8) {
            flash('error', 'Datos inválidos para reset de contraseña.');
            redirect('/admin');
        }

        (new User())->updatePassword($userId, $newPassword);
        $this->audit('reset_user_password', 'users', $userId, []);
        flash('success', 'Contraseña de usuario actualizada.');
        redirect('/admin');
    }

    public function toggleUserStatus(): void
    {
        verify_csrf();
        $userId = (int) ($_POST['user_id'] ?? 0);
        $status = sanitize_input((string) ($_POST['status'] ?? 'inactive'));

        if ($userId <= 0 || !in_array($status, ['active', 'inactive'], true)) {
            flash('error', 'Estado inválido.');
            redirect('/admin');
        }

        (new User())->setStatus($userId, $status);
        $this->audit('toggle_user_status', 'users', $userId, ['status' => $status]);
        flash('success', 'Estado de usuario actualizado.');
        redirect('/admin');
    }

    private function audit(string $action, ?string $entityType, ?int $entityId, array $meta): void
    {
        $user = Auth::user();
        (new AuditLog())->create([
            'user_id' => $user['id'] ?? null,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'metadata' => $meta,
        ]);
    }

    private function slugify(string $text): string
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}]+/u', '-', $text) ?? $text;
        return trim($text, '-') ?: 'kb-doc';
    }

    private function defaultSystemPrompt(): string
    {
        return "Eres un asistente legal para abogados peruanos.\n"
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
    }
}
