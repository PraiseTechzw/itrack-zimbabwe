<?php
require_once dirname(__DIR__) . '/middleware/AuthMiddleware.php';
class PermissionMiddleware { public static function handle(array $roles): void { AuthMiddleware::handle(); $role=$_SESSION['user']['role']??''; if($role==='admin')$role='Administrator'; if(!in_array($role,$roles,true)){http_response_code(403);exit('Forbidden');} } }
