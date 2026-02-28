<?php

declare(strict_types=1);

namespace App\Services;

final class DocumentParseService
{
    public function parse(string $absolutePath, string $mime): array
    {
        $text = '';
        $warnings = [];

        if ($mime === 'application/pdf') {
            $text = $this->parsePdf($absolutePath, $warnings);

            if (trim($text) === '') {
                $hasOcrBinary = $this->hasBinary('tesseract') || $this->hasBinary('ocrmypdf');
                if (!$hasOcrBinary) {
                    $warnings[] = 'PDF con texto vacío: posible escaneado, requiere OCR (no se detectaron binarios OCR).';
                } else {
                    $warnings[] = 'PDF con texto vacío: posible escaneado, se detectó binario OCR disponible.';
                }
            }
        } elseif ($mime === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            $text = $this->parseDocx($absolutePath);
        } else {
            $warnings[] = 'Formato no soportado para parseo.';
        }

        $chunks = $this->chunkText($text);
        $status = count($chunks) > 0 ? 'processed' : 'error';

        return [
            'text' => $text,
            'chunks' => $chunks,
            'status' => $status,
            'warnings' => $warnings,
        ];
    }

    private function parsePdf(string $path, array &$warnings): string
    {
        if ($this->hasBinary('pdftotext')) {
            $cmd = 'pdftotext ' . escapeshellarg($path) . ' -';
            $output = shell_exec($cmd);
            if (is_string($output) && trim($output) !== '') {
                return $this->normalize($output);
            }
        }

        $content = @file_get_contents($path);
        if ($content === false) {
            return '';
        }

        preg_match_all('/\(([^\)]{1,400})\)/', $content, $matches);
        $parts = $matches[1] ?? [];
        if (count($parts) === 0) {
            $warnings[] = 'No se encontró texto embebido en PDF (parser PHP básico).';
            return '';
        }

        $text = implode("\n", array_map(static fn($t) => preg_replace('/[^\PC\s]/u', '', (string) $t) ?? '', $parts));
        return $this->normalize($text);
    }

    private function parseDocx(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            return '';
        }

        $xml = $zip->getFromName('word/document.xml') ?: '';
        $zip->close();

        if ($xml === '') {
            return '';
        }

        $xml = preg_replace('/<w:p[^>]*>/', "\n", $xml) ?? $xml;
        $xml = preg_replace('/<[^>]+>/', ' ', $xml) ?? $xml;
        $xml = html_entity_decode($xml, ENT_QUOTES | ENT_XML1, 'UTF-8');

        return $this->normalize($xml);
    }

    private function chunkText(string $text, int $min = 800, int $max = 1500): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $chunks = [];
        $len = mb_strlen($text);
        $offset = 0;
        $target = 1200;

        while ($offset < $len) {
            $remaining = $len - $offset;
            if ($remaining <= $max) {
                $chunks[] = trim(mb_substr($text, $offset));
                break;
            }

            $slice = mb_substr($text, $offset, $target);
            $cut = mb_strrpos($slice, "\n");
            if ($cut === false || $cut < $min) {
                $cut = mb_strrpos($slice, '. ');
            }
            if ($cut === false || $cut < $min) {
                $cut = $target;
            }

            $chunks[] = trim(mb_substr($text, $offset, $cut));
            $offset += $cut;
        }

        return array_values(array_filter($chunks, static fn($c) => $c !== ''));
    }

    private function normalize(string $text): string
    {
        $text = preg_replace('/\r\n|\r/', "\n", $text) ?? $text;
        $text = preg_replace('/[ \t]+/', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/', "\n\n", $text) ?? $text;
        return trim($text);
    }

    private function hasBinary(string $name): bool
    {
        $result = shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null');
        return is_string($result) && trim($result) !== '';
    }
}
