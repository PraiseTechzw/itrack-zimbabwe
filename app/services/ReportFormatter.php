<?php

final class ReportFormatter
{
    private const LABELS = [
        'sale_type' => [
            'speed_certificates_only' => 'Speed limits certificates only',
            'speed_certificates_tracking' => 'Speed limits certificates and tracking',
        ],
        'package_type' => [
            'basic_installation' => 'Basic installation',
            'basic_fls_camera' => 'Basic FLS + camera installation',
            'basic_fls' => 'Basic + FLS',
        ],
    ];

    public static function rows(array $rows): array
    {
        foreach ($rows as &$row) {
            foreach ($row as $column => &$value) {
                if ($value === null) continue;
                $column = strtolower((string) $column);
                $raw = (string) $value;
                $lookup = strtolower($raw);
                if (isset(self::LABELS[$column][$lookup])) {
                    $value = self::LABELS[$column][$lookup];
                } elseif ($column === 'status') {
                    $value = ucwords(str_replace('_', ' ', $raw));
                }
            }
            unset($value);
        }
        unset($row);
        return $rows;
    }

    public static function displayValue(string $column, mixed $value): string
    {
        if ($value === null) return '—';
        if (is_numeric($value) && preg_match('/amount|price|value|balance|debit|credit/i', $column)) {
            return number_format((float) $value, 2, '.', ',');
        }
        return (string) $value;
    }
}
