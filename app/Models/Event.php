<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Event
{
    public function create(array $data): int
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO events (user_id, conversation_id, title, description, location, starts_at, ends_at, created_at, updated_at)
             VALUES (:user_id, :conversation_id, :title, :description, :location, :starts_at, :ends_at, NOW(), NOW())'
        );

        $stmt->execute([
            'user_id' => $data['user_id'],
            'conversation_id' => $data['conversation_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'location' => $data['location'],
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
        ]);

        return (int) Database::connection()->lastInsertId();
    }

    public function allByUser(int $userId): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM events WHERE user_id = :user_id ORDER BY starts_at DESC, id DESC'
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll() ?: [];
    }

    public function allByUserBetween(int $userId, string $fromDateTime, string $toDateTime): array
    {
        $stmt = Database::connection()->prepare(
            'SELECT *
             FROM events
             WHERE user_id = :user_id
               AND starts_at >= :from_dt
               AND starts_at <= :to_dt
             ORDER BY starts_at ASC, id ASC'
        );
        $stmt->execute([
            'user_id' => $userId,
            'from_dt' => $fromDateTime,
            'to_dt' => $toDateTime,
        ]);

        return $stmt->fetchAll() ?: [];
    }

    public function findByIdForUser(int $id, int $userId): ?array
    {
        $stmt = Database::connection()->prepare(
            'SELECT * FROM events WHERE id = :id AND user_id = :user_id LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'user_id' => $userId,
        ]);

        $row = $stmt->fetch();
        return $row ?: null;
    }
}
