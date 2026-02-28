<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class User
{
    public function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT id, name, email, role, status, created_at FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function all(): array
    {
        $stmt = Database::connection()->query('SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function createAdmin(string $name, string $email, string $password): void
    {
        $stmt = Database::connection()->prepare('INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (:name,:email,:password_hash,:role,:status,NOW())');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'ADMIN',
            'status' => 'active',
        ]);
    }

    public function createUser(string $name, string $email, string $password, string $role = 'USER'): int
    {
        $stmt = Database::connection()->prepare('INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (:name,:email,:password_hash,:role,:status,NOW())');
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'status' => 'active',
        ]);
        return (int) Database::connection()->lastInsertId();
    }

    public function setStatus(int $id, string $status): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET status=:status, updated_at=NOW() WHERE id=:id');
        $stmt->execute(['id' => $id, 'status' => $status]);
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $stmt = Database::connection()->prepare('SELECT password_hash FROM users WHERE id=:id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ? password_verify($password, $row['password_hash']) : false;
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $stmt = Database::connection()->prepare('UPDATE users SET password_hash=:password_hash, updated_at=NOW() WHERE id=:id');
        $stmt->execute([
            'id' => $id,
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);
    }
}
