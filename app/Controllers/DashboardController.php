<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Setting;

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
        $defaultPrompt = $this->defaultSystemPrompt();
        $prompt = (new Setting())->get('openrouter_system_prompt', $defaultPrompt);

        view('dashboard/admin', [
            'title' => 'Panel Admin',
            'user' => $user,
            'systemPrompt' => $prompt,
        ]);
    }

    public function saveSystemPrompt(): void
    {
        verify_csrf();

        $prompt = trim((string) ($_POST['system_prompt'] ?? ''));
        if ($prompt === '') {
            flash('error', 'El system prompt no puede estar vacío.');
            redirect('/admin');
        }

        (new Setting())->set('openrouter_system_prompt', $prompt);
        flash('success', 'System prompt actualizado correctamente.');
        redirect('/admin');
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
