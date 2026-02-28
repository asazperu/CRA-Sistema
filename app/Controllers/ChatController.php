<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\AnalysisRun;
use App\Models\ApiUsageLog;
use App\Models\Conversation;
use App\Models\Flag;
use App\Models\Message;
use App\Models\Setting;
use App\Services\CaseAnalysisService;
use App\Services\OpenRouterService;

final class ChatController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        $conversationModel = new Conversation();
        $conversations = $conversationModel->allByUser((int) $user['id']);

        $activeId = isset($_GET['id']) ? (int) $_GET['id'] : ($conversations[0]['id'] ?? null);
        $activeConversation = $activeId ? $conversationModel->find((int) $activeId, (int) $user['id']) : null;
        $messages = $activeConversation ? (new Message())->allByConversation((int) $activeConversation['id']) : [];
        $kbSources = [];
        if ($activeConversation) {
            $kbSources = $_SESSION['kb_sources'][(int) $activeConversation['id']] ?? [];
        }

        view('chat/index', [
            'title' => 'Asistente Legal IA',
            'user' => $user,
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
            'kbSources' => $kbSources,
        ]);
    }

    public function create(): void
    {
        verify_csrf();
        $user = Auth::user();

        $title = sanitize_input((string) ($_POST['title'] ?? 'Nueva consulta legal'));
        if ($title === '') {
            $title = 'Nueva consulta legal';
        }
        $id = (new Conversation())->create((int) $user['id'], mb_substr($title, 0, 180));
        redirect('/chat?id=' . $id);
    }

    public function rename(): void
    {
        verify_csrf();
        $user = Auth::user();
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $title = sanitize_input((string) ($_POST['title'] ?? ''));

        if ($conversationId <= 0 || $title === '') {
            flash('error', 'Título inválido.');
            redirect('/chat?id=' . $conversationId);
        }

        (new Conversation())->rename($conversationId, (int) $user['id'], mb_substr($title, 0, 180));
        redirect('/chat?id=' . $conversationId);
    }

    public function delete(): void
    {
        verify_csrf();
        $user = Auth::user();
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);

        if ($conversationId > 0) {
            (new Conversation())->delete($conversationId, (int) $user['id']);
        }

        redirect('/chat');
    }

    public function storeMessage(): void
    {
        verify_csrf();
        $user = Auth::user();
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $content = trim((string) ($_POST['content'] ?? ''));
        $streamMode = (($_POST['stream_mode'] ?? '0') === '1');

        $conversationModel = new Conversation();
        $conversation = $conversationModel->find($conversationId, (int) $user['id']);

        if (!$conversation || $content === '') {
            flash('error', 'No se pudo enviar el mensaje.');
            redirect('/chat?id=' . $conversationId);
        }

        $messageModel = new Message();
        $messageModel->create($conversationId, 'user', $content);

        $historyRows = $messageModel->allByConversation($conversationId);
        $systemPrompt = (new Setting())->get('openrouter_system_prompt',
            "Eres un asistente legal para abogados peruanos. " .
            "Responde únicamente sobre derecho peruano. " .
            "Formato: Resumen ejecutivo, Base normativa, Jurisprudencia/Criterios, Aplicación, Riesgos, Recomendaciones, Checklist. " .
            "No inventes citas; si falta información, indícalo. Incluye disclaimer final."
        );

        $analysis = (new CaseAnalysisService())->buildContextAndFlags((int) $user['id'], $conversationId, $content);
        $contextPrompt = "Contexto documental relevante (fragmentos citados):\n" . ($analysis['context'] !== '' ? $analysis['context'] : 'Sin fragmentos relevantes.')
            . "\n\nBanderas de análisis detectadas:\n"
            . (count($analysis['flags']) > 0 ? implode("\n", array_map(static fn($f) => '- ' . $f['flag_type'] . ': ' . $f['message'], $analysis['flags'])) : '- Sin banderas automáticas.');

        $analysisRunId = (new AnalysisRun())->create([
            'user_id' => (int) $user['id'],
            'conversation_id' => $conversationId,
            'query_text' => $content,
            'context_excerpt' => mb_substr($analysis['context'], 0, 4000),
            'tokens_est' => (int) ceil(strlen($analysis['context']) / 4),
            'status' => 'ok',
        ]);

        if (count($analysis['flags']) > 0) {
            (new Flag())->createMany($analysisRunId, $conversationId, $analysis['flags']);
        }

        $_SESSION['kb_sources'][$conversationId] = $analysis['kb_titles'] ?? [];

        $apiMessages = [[
            'role' => 'system',
            'content' => (string) $systemPrompt,
        ], [
            'role' => 'system',
            'content' => $contextPrompt,
        ]];

        foreach ($historyRows as $row) {
            $role = $row['sender'] === 'assistant' ? 'assistant' : 'user';
            if ($row['sender'] === 'system') {
                $role = 'system';
            }
            $apiMessages[] = ['role' => $role, 'content' => (string) $row['content']];
        }

        $logger = new ApiUsageLog();

        try {
            $response = (new OpenRouterService())->chat($apiMessages, $streamMode);

            $assistantText = trim((string) ($response['content'] ?? ''));
            $messageModel->create($conversationId, 'assistant', $assistantText);
            $conversationModel->touch($conversationId);

            $logger->create([
                'user_id' => (int) $user['id'],
                'conversation_id' => $conversationId,
                'provider' => 'openrouter',
                'model' => $response['model'] ?? null,
                'http_status' => $response['status_code'] ?? 200,
                'latency_ms' => $response['latency_ms'] ?? null,
                'prompt_tokens_est' => $response['prompt_tokens_est'] ?? null,
                'completion_tokens_est' => $response['completion_tokens_est'] ?? null,
                'total_tokens_est' => $response['total_tokens_est'] ?? null,
                'stream_mode' => $response['stream'] ?? false,
                'error_message' => null,
            ]);
        } catch (\Throwable $e) {
            $logger->create([
                'user_id' => (int) $user['id'],
                'conversation_id' => $conversationId,
                'provider' => 'openrouter',
                'model' => config('env.OPENROUTER_MODEL', 'openai/gpt-4o-mini'),
                'http_status' => 500,
                'latency_ms' => 0,
                'prompt_tokens_est' => (int) ceil(strlen($content) / 4),
                'completion_tokens_est' => 0,
                'total_tokens_est' => (int) ceil(strlen($content) / 4),
                'stream_mode' => $streamMode,
                'error_message' => $e->getMessage(),
            ]);

            flash('error', 'Error consultando OpenRouter: ' . $e->getMessage());
        }

        redirect('/chat?id=' . $conversationId);
    }
}
