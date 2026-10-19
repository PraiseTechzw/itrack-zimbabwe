<?php
require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/core/Session.php';
Session::start();
header('Content-Type: application/json; charset=utf-8');
try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        echo json_encode(['ok'=>true,'authenticated'=>!empty($_SESSION['user']),'user'=>$_SESSION['user']??null,'csrf_token'=>$_SESSION['csrf_token']], JSON_UNESCAPED_SLASHES);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Method not allowed']); exit; }
    $csrf = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf)) { http_response_code(403); echo json_encode(['ok'=>false,'error'=>'Invalid security token']); exit; }
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $user = (new User())->authenticate($email, $password);
    if (!$user) { http_response_code(401); echo json_encode(['ok'=>false,'error'=>'Invalid credentials']); exit; }
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['user'] = $user;
    echo json_encode(['ok'=>true,'user'=>$user], JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Authentication failed']); }
