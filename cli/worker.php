#!/usr/bin/env php
<?php

declare(strict_types=1);

use App\Core\Autoloader;
use App\Core\Config;
use App\Services\DocumentProcessingService;

$basePath = dirname(__DIR__);

require_once $basePath . '/app/Core/helpers.php';
require_once $basePath . '/app/Core/Autoloader.php';

$autoloader = new Autoloader($basePath . '/app');
$autoloader->register();

Config::load($basePath);
date_default_timezone_set(config('app.timezone', 'America/Lima'));

$options = getopt('', ['limit::', 'user::']);
$limit = isset($options['limit']) ? max(1, (int) $options['limit']) : 10;
$userId = isset($options['user']) ? max(1, (int) $options['user']) : null;

$start = microtime(true);

echo "== CRA Worker: procesamiento de documentos pending ==\n";
echo "limit={$limit}" . ($userId ? " user={$userId}" : '') . "\n";

try {
    $summary = (new DocumentProcessingService())->processPending($limit, $userId);
} catch (Throwable $e) {
    fwrite(STDERR, '[ERROR] Worker falló: ' . $e->getMessage() . "\n");
    exit(1);
}

foreach ($summary['details'] as $detail) {
    $line = sprintf(
        '- doc=%d status=%s chunks=%s',
        (int) ($detail['document_id'] ?? 0),
        (string) ($detail['status'] ?? 'unknown'),
        isset($detail['chunks']) ? (string) $detail['chunks'] : '-'
    );
    if (!empty($detail['warning'])) {
        $line .= ' warning=' . (string) $detail['warning'];
    }
    echo $line . "\n";
}

$elapsedMs = (int) round((microtime(true) - $start) * 1000);
echo sprintf(
    "Resumen: scanned=%d processed=%d errors=%d elapsed_ms=%d\n",
    (int) $summary['scanned'],
    (int) $summary['processed'],
    (int) $summary['errors'],
    $elapsedMs
);

echo "Sugerencia cron HostGator: */5 * * * * /usr/local/bin/php " . $basePath . "/cli/worker.php --limit=10\n";

exit(((int) $summary['errors']) > 0 ? 2 : 0);
