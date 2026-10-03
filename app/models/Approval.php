<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Approval extends ModuleModel { public function forRequisition(int $id): array{return $this->fetchAll('SELECT a.*,u.name AS approver_name FROM approvals a LEFT JOIN users u ON u.id=a.approver_id WHERE a.requisition_id=? ORDER BY a.id DESC',[$id]);} }
