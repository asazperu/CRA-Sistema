<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class AnalysisRun
{
    public function create(array $data): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO analysis_runs (user_id, conversation_id, query_text, context_excerpt, tokens_est, status, created_at) VALUES (:user_id,:conversation_id,:query_text,:context_excerpt,:tokens_est,:status,NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'conversation_id' => $data['conversation_id'],
            'query_text' => $data['query_text'],
            'context_excerpt' => $data['context_excerpt'] ?? null,
            'tokens_est' => $data['tokens_est'] ?? null,
            'status' => $data['status'] ?? 'ok',
        ]);

        return (int) Database::connection()->lastInsertId();
    }
}
