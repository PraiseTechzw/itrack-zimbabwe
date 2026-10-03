<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class DeliveryNote extends ModuleModel { public function all():array{return $this->fetchAll('SELECT d.*,u.name AS issuer_name FROM delivery_notes d LEFT JOIN users u ON u.id=d.issued_by ORDER BY d.id DESC');} }
