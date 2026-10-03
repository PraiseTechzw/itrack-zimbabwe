<?php
require_once dirname(__DIR__) . '/app/models/User.php';
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json; charset=utf-8');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo json_encode(['ok'=>true,'authenticated'=>!empty($_SESSION['user']),'user'=>$_SESSION['user']??null], JSON_UNESCAPED_SLASHES);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit; }
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $user = (new User())->authenticate($email, $password);
    if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Invalid credentials']); exit; }
    $_SESSION['user'] = $user;
    echo json_encode(['ok'=>true,'user'=>$user], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Authentication failed']); }
