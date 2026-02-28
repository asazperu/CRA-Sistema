<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class DocumentText
{
    public function replaceChunks(int $documentId, array $chunks): void
    {
        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $del = $pdo->prepare('DELETE FROM document_texts WHERE document_id=:document_id');
            $del->execute(['document_id' => $documentId]);

            $ins = $pdo->prepare('INSERT INTO document_texts (document_id, chunk_index, content, created_at) VALUES (:document_id,:chunk_index,:content,NOW())');
            foreach ($chunks as $idx => $chunk) {
                $ins->execute([
                    'document_id' => $documentId,
                    'chunk_index' => $idx,
                    'content' => $chunk,
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
