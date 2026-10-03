<?php
require_once dirname(__DIR__) . '/app/models/Requisition.php';
header('Content-Type: application/json; charset=utf-8');
try { echo json_encode(['ok'=>true,'requisitions'=>(new Requisition())->all()], JSON_UNESCAPED_SLASHES); }
catch (Throwable $e) { http_response_code(500); echo json_encode(['ok'=>false,'error'=>'Unable to load requisitions']); }
