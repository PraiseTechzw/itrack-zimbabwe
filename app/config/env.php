<?php

function loadEnv(): array
{
    static $loaded = null;
    if ($loaded !== null) {
        return $loaded;
    }

    $loaded = [];
    $envPath = dirname(__DIR__, 2) . '/.env';
    if (!is_readable($envPath)) {
        return $loaded;
    }

    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);

        if ($value === '') {
            $loaded[$name] = '';
            continue;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        $loaded[$name] = $value;
    }

    return $loaded;
}

function env(string $key, $default = null)
{
    $vars = loadEnv();
    if (array_key_exists($key, $vars)) {
        return $vars[$key];
    }

    $value = getenv($key);
    return $value !== false ? $value : $default;
}
