<?php

require_once __DIR__ . '/Session.php';

class Security
{
    public static function token(): string
    {
        Session::start();
        return $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
    }

    public static function verify(string $token): bool
    {
        return hash_equals(self::token(), $token);
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
