<?php

function debugLog(string $message, array $context = []): void
{
    $config = require dirname(__DIR__) . '/config/app.php';
    if (empty($config['debug'])) {
        return;
    }

    $logDir = dirname(__DIR__, 2) . '/storage/logs';
    if (!is_dir($logDir)) {
        mkdir($logDir, 0777, true);
    }

    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'message' => $message,
        'context' => $context,
        'request_uri' => $_SERVER['REQUEST_URI'] ?? null,
        'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
    ];

    $payload = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    $targetFile = $logDir . '/debug.log';

    if (@file_put_contents($targetFile, $payload, FILE_APPEND | LOCK_EX) === false) {
        error_log('DEBUG LOGGER FAILED WRITING TO ' . $targetFile . ': ' . $payload);
    }
}

function consoleLog(string $message, array $context = []): string
{
    $payload = [
        'message' => $message,
        'context' => $context,
    ];
    $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    return '<script>console.log(' . json_encode($json, JSON_UNESCAPED_UNICODE) . ');</script>';
}
