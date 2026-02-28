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

    public function searchRelevant(int $userId, int $conversationId, array $keywords, int $limit = 8): array
    {
        $where = '(d.user_id = :user_id AND (d.conversation_id = :conversation_id OR d.conversation_id IS NULL))';
        $params = [
            'user_id' => $userId,
            'conversation_id' => $conversationId,
        ];

        $keywordSql = '';
        if (count($keywords) > 0) {
            $parts = [];
            foreach ($keywords as $i => $kw) {
                $key = 'kw' . $i;
                $parts[] = 'dt.content LIKE :' . $key;
                $params[$key] = '%' . $kw . '%';
            }
            $keywordSql = ' AND (' . implode(' OR ', $parts) . ')';
        }

        $sql = 'SELECT dt.*, d.original_name, d.storage_path
                FROM document_texts dt
                INNER JOIN documents d ON d.id = dt.document_id
                WHERE ' . $where . $keywordSql . '
                ORDER BY dt.created_at DESC
                LIMIT ' . (int) $limit;

        $stmt = Database::connection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
