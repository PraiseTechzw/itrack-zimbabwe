<?php

require_once dirname(__DIR__) . '/helpers/settings.php';

final class Session
{
    public static function start(): void
    {
        if (session_status() !== PHP_SESSION_NONE) return;

        $config = require dirname(__DIR__) . '/config/app.php';
        session_name((string) $config['session_name']);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        $lifetimeMinutes = 120;
        try {
            $lifetimeMinutes = setting_int('session_lifetime_minutes', 120);
        } catch (Throwable) {
            // Fall back to the default when the settings store is not ready.
        }
        session_set_cookie_params([
            'lifetime' => max(15, $lifetimeMinutes) * 60,
            'path' => '/',
            'secure' => !empty($config['session_secure']) || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }
}
