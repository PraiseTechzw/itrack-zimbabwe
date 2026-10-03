<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Sale extends ModuleModel {
    public function all(): array { return $this->fetchAll("SELECT s.*, c.company_name AS client_name, u.name AS sales_person_name FROM sales s LEFT JOIN clients c ON c.id=s.client_id LEFT JOIN users u ON u.id=s.sales_person_id ORDER BY s.id DESC"); }
    public function create(array $data): string { $this->execute('INSERT INTO sales (client_id, sales_person_id, sale_date, status, total_amount) VALUES (?, ?, ?, ?, ?)', [(int)($data['client_id']??0) ?: null, (int)($data['sales_person_id']??0) ?: null, ($data['sale_date'] ?? '') ?: date('Y-m-d'), ($data['status'] ?? '') ?: 'draft', (float)($data['total_amount']??0)]); return $this->lastInsertId(); }
    public function updateStatus(int $id, string $status): void { $this->execute('UPDATE sales SET status=? WHERE id=?', [$status,$id]); }
    public function count(): int { return $this->tableCount('sales'); }
    public function total(): float { return $this->tableTotal('sales','total_amount'); }
}
