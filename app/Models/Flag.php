<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Flag
{
    public function createMany(int $analysisRunId, int $conversationId, array $flags): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO flags (analysis_run_id, conversation_id, flag_type, severity, message, created_at) VALUES (:analysis_run_id,:conversation_id,:flag_type,:severity,:message,NOW())');

        foreach ($flags as $f) {
            $stmt->execute([
                'analysis_run_id' => $analysisRunId,
                'conversation_id' => $conversationId,
                'flag_type' => $f['flag_type'],
                'severity' => $f['severity'] ?? 'medium',
                'message' => $f['message'],
            ]);
        }
    }
}
