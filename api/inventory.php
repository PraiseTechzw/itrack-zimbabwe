<?php
require_once dirname(__DIR__) . '/app/models/Product.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $model = new Product();
    echo json_encode(['ok' => true, 'products' => $model->all()], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to load inventory']);
}
