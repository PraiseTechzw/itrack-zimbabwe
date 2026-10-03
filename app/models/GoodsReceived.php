<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class GoodsReceived extends ModuleModel { public function all():array{return $this->fetchAll('SELECT g.*,po.total_amount,u.name AS receiver_name FROM goods_received g LEFT JOIN purchase_orders po ON po.id=g.purchase_order_id LEFT JOIN users u ON u.id=g.received_by ORDER BY g.id DESC');} }
