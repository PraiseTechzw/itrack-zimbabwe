<?php
require_once dirname(__DIR__) . '/app/config/database.php';
header('Content-Type: application/json; charset=utf-8');
try {
    $db = Database::getInstance();
    $rows = static function (PDO $db, string $sql): array { return $db->query($sql)->fetchAll(); };
    $payload = [
        'ok' => true,
        'summary' => [
            'invoices' => (int) $db->query('SELECT COUNT(*) FROM invoices')->fetchColumn(),
            'payments' => (int) $db->query('SELECT COUNT(*) FROM payments')->fetchColumn(),
            'expenses' => (int) $db->query('SELECT COUNT(*) FROM expenses')->fetchColumn(),
            'cash_balance' => (float) $db->query('SELECT COALESCE((SELECT balance FROM cash_book ORDER BY id DESC LIMIT 1), 0)')->fetchColumn(),
        ],
        'cash_book' => $rows($db, 'SELECT * FROM cash_book ORDER BY id DESC LIMIT 50'),
        'expenses' => $rows($db, 'SELECT * FROM expenses ORDER BY id DESC LIMIT 50'),
        'payments' => $rows($db, 'SELECT * FROM payments ORDER BY id DESC LIMIT 50'),
    ];
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Unable to load accounting data']);
}
