<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Stock extends ModuleModel { public function all():array{return $this->fetchAll('SELECT s.*,p.name AS product_name,p.sku FROM stocks s JOIN products p ON p.id=s.product_id ORDER BY p.name');} public function quantity(int $productId):int{return (int)$this->fetchOne('SELECT COALESCE(SUM(quantity),0) AS quantity FROM stocks WHERE product_id=?',[$productId])['quantity'];} }
