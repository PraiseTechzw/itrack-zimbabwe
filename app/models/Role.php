<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Role extends ModuleModel { public function all():array{return $this->fetchAll('SELECT role,COUNT(*) AS permission_count FROM role_permissions GROUP BY role ORDER BY role');} }
