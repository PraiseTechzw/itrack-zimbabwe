<?php
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
class AdminMiddleware { public static function handle(): void { AuthMiddleware::handle(); $role=$_SESSION['user']['role']??''; if($role==='admin')$role='Administrator'; if($role!=='Administrator'){http_response_code(403);exit('Forbidden');} } }
