<?php

require_once dirname(__DIR__) . '/models/AppSetting.php';

if (!function_exists('app_setting')) {
    /** Read a saved setting with an environment/default fallback. */
    function app_setting(string $key, mixed $fallback = null): mixed
    {
        static $cache = null;
        if ($cache === null) {
            try {
                $cache = (new AppSetting())->all();
            } catch (Throwable) {
                $cache = [];
            }
        }
        if (array_key_exists($key, $cache)) return $cache[$key];
        return AppSetting::defaults()[$key] ?? $fallback;
    }

    function setting_string(string $key, string $fallback = ''): string { return trim((string) app_setting($key, $fallback)); }

    function setting_bool(string $key, bool $fallback = false): bool
    {
        $value = app_setting($key, $fallback ? '1' : '0');
        return !in_array(strtolower((string) $value), ['', '0', 'false', 'no', 'off'], true);
    }

    function setting_int(string $key, int $fallback = 0): int { return (int) app_setting($key, $fallback); }

    function setting_float(string $key, float $fallback = 0.0): float { return (float) app_setting($key, $fallback); }

    function money_format_setting(float $amount): string
    {
        return setting_string('currency_symbol', '$') . number_format($amount, 2);
    }
}
