<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Conversation;
use App\Models\Message;

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

        view('chat/index', [
            'title' => 'Asistente Legal IA',
            'user' => $user,
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'messages' => $messages,
        ]);
    }

    public function create(): void
    {
        verify_csrf();
        $user = Auth::user();

        $title = trim($_POST['title'] ?? 'Nueva consulta legal');
        $id = (new Conversation())->create((int) $user['id'], $title);
        redirect('/chat?id=' . $id);
    }

    public function show(): void
    {
        redirect('/chat?id=' . (int) ($_GET['id'] ?? 0));
    }

    public function storeMessage(): void
    {
        verify_csrf();
        $user = Auth::user();
        $conversationId = (int) ($_POST['conversation_id'] ?? 0);
        $content = trim($_POST['content'] ?? '');

        $conversationModel = new Conversation();
        $conversation = $conversationModel->find($conversationId, (int) $user['id']);

        if (!$conversation || $content === '') {
            flash('error', 'No se pudo enviar el mensaje.');
            redirect('/chat?id=' . $conversationId);
        }

        $messageModel = new Message();
        $messageModel->create($conversationId, 'user', $content);

        $aiResponse = "Enfoque legal peruano (referencial):\n\n1) Identifique hechos y documentos (DNI, contratos, boletas, notificaciones).\n2) Defina vía: conciliación, demanda civil, laboral, penal o administrativa.\n3) Verifique plazos de prescripción/caducidad y competencia territorial.\n4) Prepare estrategia probatoria y costos.\n\nConsulta del cliente: \"{$content}\"\n\n⚠️ Este sistema brinda orientación general y no reemplaza asesoría profesional.";

        $messageModel->create($conversationId, 'assistant', $aiResponse);
        $conversationModel->touch($conversationId);

        redirect('/chat?id=' . $conversationId);
    }
}
