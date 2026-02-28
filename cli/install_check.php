#!/usr/bin/env php
<?php

declare(strict_types=1);

$lock = __DIR__ . '/../install.lock';
$env = __DIR__ . '/../.env';

echo '== Castro Romero Abogados :: install-check ==' . PHP_EOL;
echo is_file($lock) ? "[OK] install.lock presente\n" : "[WARN] install.lock no encontrado\n";
echo is_file($env) ? "[OK] .env presente\n" : "[WARN] .env no encontrado\n";
