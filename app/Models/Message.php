<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Message
{
    public function allByConversation(int $conversationId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM messages WHERE conversation_id = :conversation_id ORDER BY created_at ASC');
        $stmt->execute(['conversation_id' => $conversationId]);
        return $stmt->fetchAll();
    }

    public function create(int $conversationId, string $sender, string $content): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO messages (conversation_id,sender,content,created_at) VALUES (:conversation_id,:sender,:content,NOW())');
        $stmt->execute([
            'conversation_id' => $conversationId,
            'sender' => $sender,
            'content' => $content,
        ]);
    }
}
