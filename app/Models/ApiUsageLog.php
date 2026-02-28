<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class ApiUsageLog
{
    public function create(array $data): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO api_usage_logs (user_id, conversation_id, provider, model, endpoint, http_status, latency_ms, prompt_tokens_est, completion_tokens_est, total_tokens_est, stream_mode, error_message, created_at)
             VALUES (:user_id, :conversation_id, :provider, :model, :endpoint, :http_status, :latency_ms, :prompt_tokens_est, :completion_tokens_est, :total_tokens_est, :stream_mode, :error_message, NOW())'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'conversation_id' => $data['conversation_id'],
            'provider' => $data['provider'] ?? 'openrouter',
            'model' => $data['model'] ?? null,
            'endpoint' => $data['endpoint'] ?? '/api/v1/chat/completions',
            'http_status' => $data['http_status'] ?? null,
            'latency_ms' => $data['latency_ms'] ?? null,
            'prompt_tokens_est' => $data['prompt_tokens_est'] ?? null,
            'completion_tokens_est' => $data['completion_tokens_est'] ?? null,
            'total_tokens_est' => $data['total_tokens_est'] ?? null,
            'stream_mode' => !empty($data['stream_mode']) ? 1 : 0,
            'error_message' => $data['error_message'] ?? null,
        ]);
    }
}
