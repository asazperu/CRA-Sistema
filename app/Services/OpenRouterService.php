<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Setting;
use RuntimeException;

final class OpenRouterService
{
    private const ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    public function chat(array $messages, bool $stream = false): array
    {
        $apiKey = (string) config('env.OPENROUTER_API_KEY', '');
        if ($apiKey === '') {
            throw new RuntimeException('OPENROUTER_API_KEY no está configurada en .env');
        }

        $settings = new Setting();
        $model = (string) ($settings->get('ai_model', config('env.OPENROUTER_MODEL', 'openai/gpt-4o-mini')) ?? 'openai/gpt-4o-mini');
        $temperature = (float) ($settings->get('ai_temperature', '0.2') ?? '0.2');
        $maxTokens = (int) ($settings->get('ai_max_tokens', '1200') ?? '1200');

        $appUrl = (string) config('env.APP_URL', config('app.url', ''));
        $appName = (string) config('env.APP_NAME', config('app.name', 'CRA Legal IA'));

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens,
            'stream' => $stream,
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
            'HTTP-Referer: ' . ($appUrl !== '' ? $appUrl : 'https://localhost'),
            'X-Title: ' . $appName,
        ];

        $ch = curl_init(self::ENDPOINT);
        $buffer = '';
        $assistantText = '';
        $rawBody = '';

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_RETURNTRANSFER => !$stream,
            CURLOPT_TIMEOUT => 120,
        ]);

        if ($stream) {
            curl_setopt($ch, CURLOPT_WRITEFUNCTION, function ($curl, $chunk) use (&$buffer, &$assistantText, &$rawBody): int {
                $rawBody .= $chunk;
                $buffer .= $chunk;

                while (($pos = strpos($buffer, "\n")) !== false) {
                    $line = trim(substr($buffer, 0, $pos));
                    $buffer = substr($buffer, $pos + 1);
                    if (!str_starts_with($line, 'data:')) {
                        continue;
                    }
                    $data = trim(substr($line, 5));
                    if ($data === '' || $data === '[DONE]') {
                        continue;
                    }
                    $json = json_decode($data, true);
                    $delta = $json['choices'][0]['delta']['content'] ?? '';
                    if (is_string($delta)) {
                        $assistantText .= $delta;
                    }
                }
                return strlen($chunk);
            });
        }

        $start = microtime(true);
        $result = curl_exec($ch);
        $latencyMs = (int) round((microtime(true) - $start) * 1000);

        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException('Error cURL OpenRouter: ' . $error);
        }

        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!$stream) {
            $rawBody = is_string($result) ? $result : '';
            $json = json_decode($rawBody, true);
            $assistantText = (string) ($json['choices'][0]['message']['content'] ?? '');
        }

        if ($statusCode >= 400 || $assistantText === '') {
            throw new RuntimeException('OpenRouter devolvió error HTTP ' . $statusCode . '. Respuesta: ' . substr($rawBody, 0, 1000));
        }

        $promptChars = 0;
        foreach ($messages as $m) {
            $promptChars += strlen((string) ($m['content'] ?? ''));
        }
        $promptTokens = max(1, (int) ceil($promptChars / 4));
        $completionTokens = max(1, (int) ceil(strlen($assistantText) / 4));

        return [
            'model' => $model,
            'content' => $assistantText,
            'status_code' => $statusCode,
            'latency_ms' => $latencyMs,
            'prompt_tokens_est' => $promptTokens,
            'completion_tokens_est' => $completionTokens,
            'total_tokens_est' => $promptTokens + $completionTokens,
            'stream' => $stream,
        ];
    }
}
