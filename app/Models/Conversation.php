<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Conversation
{
    public function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM conversations WHERE user_id = :user_id ORDER BY updated_at DESC');
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $title): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO conversations (user_id,title,created_at,updated_at) VALUES (:user_id,:title,NOW(),NOW())');
        $stmt->execute(['user_id' => $userId, 'title' => $title]);
        return (int) Database::connection()->lastInsertId();
    }

    public function find(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM conversations WHERE id = :id AND user_id = :user_id LIMIT 1');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        return $stmt->fetch() ?: null;
    }

    public function rename(int $id, int $userId, string $title): void
    {
        $stmt = Database::connection()->prepare('UPDATE conversations SET title=:title, updated_at=NOW() WHERE id=:id AND user_id=:user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId, 'title' => $title]);
    }

    public function delete(int $id, int $userId): void
    {
        $stmt = Database::connection()->prepare('DELETE FROM conversations WHERE id=:id AND user_id=:user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
    }

    public function touch(int $id): void
    {
        $stmt = Database::connection()->prepare('UPDATE conversations SET updated_at = NOW() WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}
