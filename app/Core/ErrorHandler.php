<?php

declare(strict_types=1);

namespace App\Core;

use Throwable;

final class ErrorHandler
{
    public static function register(): void
    {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function handleException(Throwable $e): void
    {
        error_log('[APP_ERROR] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());

        $isCli = PHP_SAPI === 'cli';
        if ($isCli) {
            fwrite(STDERR, '[ERROR] ' . $e->getMessage() . PHP_EOL);
            exit(1);
        }

        $isDebug = ((string) config('env.APP_DEBUG', '0')) === '1';
        http_response_code(500);

        if ($isDebug) {
            echo '<h1>Error interno</h1><pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
            return;
        }

        echo 'Se produjo un error interno. Intente nuevamente.';
    }
}
