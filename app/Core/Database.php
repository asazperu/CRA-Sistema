<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }

        $host = config('env.DB_HOST', config('database.host', '127.0.0.1'));
        $port = config('env.DB_PORT', (string) config('database.port', 3306));
        $dbname = config('env.DB_NAME', config('database.name', ''));
        $user = config('env.DB_USER', config('database.user', 'root'));
        $pass = config('env.DB_PASS', config('database.pass', ''));
        $charset = config('database.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

        try {
            self::$pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Error de conexión DB: ' . $e->getMessage());
        }

        return self::$pdo;
    }
}
