<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentText;

final class DocumentProcessingService
{
    public function processPending(int $limit = 10, ?int $userId = null): array
    {
        $documentModel = new Document();
        $pending = $documentModel->pending($limit, $userId);

        $processed = 0;
        $errors = 0;
        $details = [];

        foreach ($pending as $doc) {
            $result = $this->processOne($doc);
            $details[] = $result;
            if ($result['status'] === 'processed') {
                $processed++;
            } else {
                $errors++;
            }
        }

        return [
            'scanned' => count($pending),
            'processed' => $processed,
            'errors' => $errors,
            'details' => $details,
        ];
    }

    public function processOne(array $document): array
    {
        $documentId = (int) $document['id'];
        $userId = (int) $document['user_id'];
        $absolute = base_path((string) $document['storage_path']);
        $model = new Document();

        if (!is_file($absolute)) {
            $warning = 'Archivo físico no encontrado para procesar.';
            $model->updateProcessingResult($documentId, $userId, 'error', $warning);
            return [
                'document_id' => $documentId,
                'status' => 'error',
                'warning' => $warning,
            ];
        }

        $result = (new DocumentParseService())->parse($absolute, (string) $document['mime_type']);

        $summary = $this->buildSummary((string) ($result['text'] ?? ''));
        $indexedChunks = $result['chunks'];
        if ($summary !== '') {
            array_unshift($indexedChunks, '[RESUMEN AUTOMÁTICO]\n' . $summary);
        }

        (new DocumentText())->replaceChunks($documentId, $indexedChunks);

        $warnings = $result['warnings'] ?? [];
        if ($summary === '') {
            $warnings[] = 'No se pudo generar resumen automático.';
        }
        $warning = count($warnings) > 0 ? implode(' | ', $warnings) : null;

        $model->updateProcessingResult($documentId, $userId, (string) $result['status'], $warning);

        return [
            'document_id' => $documentId,
            'status' => (string) $result['status'],
            'warning' => $warning,
            'chunks' => count($indexedChunks),
        ];
    }

    private function buildSummary(string $text): string
    {
        $clean = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);
        if ($clean === '') {
            return '';
        }

        $sentences = preg_split('/(?<=[\.!?])\s+/u', $clean) ?: [];
        $summaryParts = [];
        $length = 0;

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            if ($sentence === '') {
                continue;
            }
            $summaryParts[] = $sentence;
            $length += mb_strlen($sentence) + 1;
            if (count($summaryParts) >= 3 || $length >= 600) {
                break;
            }
        }

        $summary = trim(implode(' ', $summaryParts));
        return mb_substr($summary, 0, 700);
    }
}
