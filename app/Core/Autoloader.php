<?php

declare(strict_types=1);

namespace App\Core;

final class Autoloader
{
    public function __construct(private readonly string $basePath)
    {
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'load']);
    }

    private function load(string $class): void
    {
        $prefix = 'App\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relative = substr($class, strlen($prefix));
        $path = $this->basePath . '/' . str_replace('\\', '/', $relative) . '.php';

        if (is_file($path)) {
            require_once $path;
        }
    }
}
