<?php

require_once __DIR__ . '/env.php';

return [
    'app_name' => env('APP_NAME', 'iTrack Zimbabwe'),
    'environment' => strtolower((string) env('APP_ENV', 'development')),
    'db_driver' => strtolower((string) env('DB_DRIVER', 'auto')),
    'db_sqlite_path' => (string) env('DB_SQLITE_PATH', dirname(__DIR__, 2) . '/storage/itrack.sqlite'),
    'allow_registration' => filter_var(env('APP_ALLOW_REGISTRATION', !in_array(strtolower((string) env('APP_ENV', 'development')), ['prod', 'production'], true)), FILTER_VALIDATE_BOOLEAN),
    'session_secure' => filter_var(env('APP_SESSION_SECURE', false), FILTER_VALIDATE_BOOLEAN),
    'mail_from' => env('MAIL_FROM', 'no-reply@localhost'),
    'public_url' => rtrim((string) env('APP_PUBLIC_URL', ''), '/'),
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
    'openrouter_api_key' => env('OPENROUTER_API_KEY', ''),
    'openrouter_model' => env('OPENROUTER_MODEL', 'openrouter/free'),
];
