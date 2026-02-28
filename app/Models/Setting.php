<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class Setting
{
    public function get(string $key, ?string $default = null): ?string
    {
        $stmt = Database::connection()->prepare('SELECT setting_value FROM settings WHERE setting_key = :setting_key LIMIT 1');
        $stmt->execute(['setting_key' => $key]);
        $row = $stmt->fetch();
        return $row['setting_value'] ?? $default;
    }

    public function set(string $key, string $value): void
    {
        $stmt = Database::connection()->prepare(
            'INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (:setting_key, :setting_value, NOW())
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = VALUES(updated_at)'
        );
        $stmt->execute([
            'setting_key' => $key,
            'setting_value' => $value,
        ]);
    }
}
