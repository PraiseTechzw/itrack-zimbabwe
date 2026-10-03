<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Requisition extends ModuleModel {
    public function all(): array { return $this->fetchAll("SELECT r.*, u.name AS requester_name FROM requisitions r LEFT JOIN users u ON u.id=r.requested_by ORDER BY r.id DESC"); }
    public function create(array $data): string { $this->execute('INSERT INTO requisitions (requested_by, department, requested_date, status, total_amount, remarks) VALUES (?, ?, ?, ?, ?, ?)', [(int)($data['requested_by']??0) ?: null, trim($data['department']??'General'), ($data['requested_date'] ?? '') ?: date('Y-m-d'), 'pending', (float)($data['total_amount']??0), trim($data['remarks']??'')]); return $this->lastInsertId(); }
    public function updateStatus(int $id, string $status, ?int $approverId = null, string $comments = ''): void { $this->db->beginTransaction(); $this->execute('UPDATE requisitions SET status=? WHERE id=?', [$status,$id]); $this->execute('INSERT INTO approvals (requisition_id, approver_id, status, comments, decision_date) VALUES (?, ?, ?, ?, ?)', [$id,$approverId,$status,$comments,date('Y-m-d')]); $this->db->commit(); }
    public function count(): int { return $this->tableCount('requisitions'); }
    public function pendingCount(): int { return (int)$this->db->query("SELECT COUNT(*) FROM requisitions WHERE status='pending'")->fetchColumn(); }
}
