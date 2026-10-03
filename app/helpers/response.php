<?php
if (!function_exists('jsonResponse')) { function jsonResponse(array $payload, int $status=200): never { http_response_code($status); header('Content-Type: application/json; charset=utf-8'); echo json_encode($payload, JSON_UNESCAPED_SLASHES); exit; } }
