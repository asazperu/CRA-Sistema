<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Document
{
    public function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM documents WHERE user_id=:user_id ORDER BY uploaded_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO documents (user_id, conversation_id, category, original_name, stored_name, mime_type, extension, size_bytes, checksum_sha256, storage_path, processing_status, uploaded_at) VALUES (:user_id,:conversation_id,:category,:original_name,:stored_name,:mime_type,:extension,:size_bytes,:checksum_sha256,:storage_path,:processing_status,NOW())');
        $stmt->execute([
            'user_id' => $data['user_id'],
            'conversation_id' => $data['conversation_id'] ?? null,
            'category' => $data['category'] ?? null,
            'original_name' => $data['original_name'],
            'stored_name' => $data['stored_name'],
            'mime_type' => $data['mime_type'],
            'extension' => $data['extension'] ?? null,
            'size_bytes' => $data['size_bytes'],
            'checksum_sha256' => $data['checksum_sha256'] ?? null,
            'storage_path' => $data['storage_path'],
            'processing_status' => $data['processing_status'] ?? 'pending',
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM documents WHERE id=:id AND user_id=:user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM documents WHERE id=:id AND user_id=:user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function reprocess(int $id, int $userId, string $status = 'pending'): void
    {
        $stmt = Database::connection()->prepare('UPDATE documents SET processing_status=:status, processed_at = NULL WHERE id=:id AND user_id=:user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId, 'status' => $status]);
    }
}
