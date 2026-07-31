<?php

require_once __DIR__ . '/env.php';

return [
    'app_name' => env('APP_NAME', 'iTrack Zimbabwe'),
    'debug' => filter_var(env('APP_DEBUG', false), FILTER_VALIDATE_BOOLEAN),
    'base_url' => env('APP_BASE_URL', '/itrack-zimbabwe'),
    'db' => [
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'name' => env('DB_NAME', 'itrack_zimbabwe'),
        'user' => env('DB_USER', 'root'),
        'pass' => env('DB_PASS', ''),
    ],
    'session_name' => env('SESSION_NAME', 'itrack_session'),
    'csrf_token_name' => env('CSRF_TOKEN_NAME', 'csrf_token'),
    'upload_dir' => env('UPLOAD_DIR', dirname(__DIR__, 1) . '/uploads'),
];
