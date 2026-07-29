<?php

return [
    'app_name' => 'iTrack Zimbabwe',
    'base_url' => '/itrack-zimbabwe',
    'db' => [
        'host' => '127.0.0.1',
        'port' => '3306',
        'name' => 'itrack_zimbabwe',
        'user' => 'root',
        'pass' => '',
    ],
    'session_name' => 'itrack_session',
    'csrf_token_name' => 'csrf_token',
    'upload_dir' => dirname(__DIR__, 1) . '/uploads',
];
