<?php
if (!function_exists('formatMoney')) { function formatMoney(float $amount, string $currency='USD'): string { return $currency . ' ' . number_format($amount, 2); } }
if (!function_exists('formatDate')) { function formatDate(?string $date): string { return $date ? date('d M Y', strtotime($date)) : '—'; } }
