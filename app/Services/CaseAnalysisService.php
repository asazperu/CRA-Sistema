<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentText;
use App\Models\KnowledgeArticle;

final class CaseAnalysisService
{
    public function buildContextAndFlags(int $userId, int $conversationId, string $query): array
    {
        $keywords = $this->extractKeywords($query);

        $docChunks = (new DocumentText())->searchRelevant($userId, $conversationId, $keywords, 8);
        $kbArticles = (new KnowledgeArticle())->searchByKeywords($keywords, 6);

        $contextLines = [];
        foreach ($docChunks as $chunk) {
            $snippet = mb_substr(trim((string) $chunk['content']), 0, 320);
            $contextLines[] = sprintf('[CASO | Documento:%s | chunk:%d] %s', $chunk['original_name'], (int) $chunk['chunk_index'], $snippet);
        }

        // KB como segunda prioridad: se añade después del contexto documental del caso.
        foreach ($kbArticles as $kb) {
            $snippet = mb_substr(trim((string) $kb['body']), 0, 260);
            $contextLines[] = sprintf('[KB | %s] %s', (string) $kb['title'], $snippet);
        }

        $context = implode("\n", $contextLines);

        $flags = [];
        if (count($docChunks) === 0) {
            $flags[] = ['flag_type' => 'omission', 'severity' => 'high', 'message' => 'No se encontraron fragmentos del caso relevantes para la consulta.'];
        }

        if ($this->containsAny($query, ['demanda', 'apelación', 'contestación', 'recurso']) && !$this->containsAny($context, ['plazo', 'días', 'vencimiento'])) {
            $flags[] = ['flag_type' => 'procedural_risk', 'severity' => 'high', 'message' => 'Riesgo procesal: faltan referencias claras a plazos procesales.'];
        }

        if ($this->containsAny($query, ['contrato', 'obligación']) && $this->containsAny($context, ['anexo', 'adenda']) && !$this->containsAny($query, ['anexo', 'adenda'])) {
            $flags[] = ['flag_type' => 'missing_question', 'severity' => 'medium', 'message' => 'Posible pregunta faltante: precisar anexos/adendas vinculados al contrato.'];
        }

        if ($this->containsAny($context, ['sí']) && $this->containsAny($context, ['no'])) {
            $flags[] = ['flag_type' => 'contradiction', 'severity' => 'medium', 'message' => 'Posible contradicción detectada entre fragmentos documentales.'];
        }

        return [
            'keywords' => $keywords,
            'chunks' => $docChunks,
            'kb_articles' => $kbArticles,
            'kb_titles' => array_values(array_map(static fn($a) => (string) $a['title'], $kbArticles)),
            'context' => $context,
            'flags' => $flags,
        ];
    }

    private function extractKeywords(string $text): array
    {
        $text = mb_strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text) ?? $text;
        $parts = array_filter(explode(' ', $text), static fn($w) => mb_strlen($w) >= 4);
        $stop = ['para','como','este','esta','sobre','entre','desde','hasta','donde','cuando','caso','legal','peruano','peruana'];
        $parts = array_values(array_filter($parts, static fn($w) => !in_array($w, $stop, true)));
        return array_slice(array_unique($parts), 0, 12);
    }

    private function containsAny(string $text, array $needles): bool
    {
        $haystack = mb_strtolower($text);
        foreach ($needles as $n) {
            if (str_contains($haystack, mb_strtolower($n))) {
                return true;
            }
        }
        return false;
    }
}
