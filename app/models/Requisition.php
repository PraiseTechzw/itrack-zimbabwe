<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Requisition extends ModuleModel {
    public function all(): array
    {
        return $this->fetchAll('SELECT r.*, u.name AS requester_name, c.company_name AS client_name, a.name AS approver_name FROM requisitions r LEFT JOIN users u ON u.id=r.requested_by LEFT JOIN clients c ON c.id=r.client_id LEFT JOIN users a ON a.id=(SELECT ap.approver_id FROM approvals ap WHERE ap.requisition_id=r.id ORDER BY ap.id DESC LIMIT 1) ORDER BY r.id DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT r.*, u.name AS requester_name, c.company_name AS client_name FROM requisitions r LEFT JOIN users u ON u.id=r.requested_by LEFT JOIN clients c ON c.id=r.client_id WHERE r.id = :id', [':id'=>$id]);
    }

    public function create(array $data): string
    {
        $this->execute(
            'INSERT INTO requisitions (requested_by, department, purpose, requested_date, status, total_amount, remarks, request_type, client_id, beneficiary_name, requester_signature, installation_items, items_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                (int)($data['requested_by'] ?? 0) ?: null,
                trim((string)($data['department'] ?? 'General')),
                trim((string)($data['purpose'] ?? '')),
                ($data['requested_date'] ?? '') ?: date('Y-m-d'),
                'pending',
                (float)($data['total_amount'] ?? 0),
                trim((string)($data['remarks'] ?? '')),
                (string)($data['request_type'] ?? 'other'),
                (int)($data['client_id'] ?? 0) ?: null,
                trim((string)($data['beneficiary_name'] ?? '')),
                (string)($data['requester_signature'] ?? ''),
                isset($data['installation_items']) ? json_encode($data['installation_items'], JSON_THROW_ON_ERROR) : null,
                isset($data['items']) ? json_encode($data['items'], JSON_THROW_ON_ERROR) : null,
            ]
        );
        return $this->lastInsertId();
    }

    public function updateStatus(int $id, string $status, ?int $approverId = null, string $comments = '', string $signature = ''): void
    {
        $this->db->beginTransaction();
        try {
            $updated = $this->execute('UPDATE requisitions SET status=?, approver_signature=? WHERE id=? AND status=?', [$status, $signature, $id, 'pending']);
            if ($updated < 1) throw new InvalidArgumentException('This requisition has already been decided.');
            $this->execute('INSERT INTO approvals (requisition_id, approver_id, status, comments, decision_date) VALUES (?, ?, ?, ?, ?)', [$id, $approverId, $status, $comments, date('Y-m-d')]);
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
    public function count(): int { return $this->tableCount('requisitions'); }
    public function pendingCount(): int { return (int)$this->db->query("SELECT COUNT(*) FROM requisitions WHERE status='pending'")->fetchColumn(); }
}
