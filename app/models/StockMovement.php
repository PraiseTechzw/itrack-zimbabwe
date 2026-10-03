<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class StockMovement extends ModuleModel { public function all(int $limit=100):array{return $this->fetchAll('SELECT m.*,p.name AS product_name,u.name AS user_name FROM stock_movements m JOIN products p ON p.id=m.product_id LEFT JOIN users u ON u.id=m.created_by ORDER BY m.id DESC LIMIT '.max(1,(int)$limit));} }
