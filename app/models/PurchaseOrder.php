<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class PurchaseOrder extends ModuleModel {
    public function all(): array { return $this->fetchAll("SELECT po.*, s.company_name AS supplier_name, u.name AS ordered_by_name FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id LEFT JOIN users u ON u.id=po.ordered_by ORDER BY po.id DESC"); }
    public function find(int $id): ?array { return $this->fetchOne('SELECT po.*, s.company_name AS supplier_name, u.name AS ordered_by_name FROM purchase_orders po LEFT JOIN suppliers s ON s.id=po.supplier_id LEFT JOIN users u ON u.id=po.ordered_by WHERE po.id = :id', [':id' => $id]); }
    public function create(array $data): string { $this->execute('INSERT INTO purchase_orders (supplier_id, ordered_by, order_date, status, total_amount, remarks) VALUES (?, ?, ?, ?, ?, ?)', [(int)($data['supplier_id']??0) ?: null, (int)($data['ordered_by']??0) ?: null, ($data['order_date'] ?? '') ?: date('Y-m-d'), ($data['status'] ?? '') ?: 'pending', (float)($data['total_amount']??0), trim($data['remarks']??'')]); return $this->lastInsertId(); }
    public function updateStatus(int $id, string $status): void { $this->execute('UPDATE purchase_orders SET status=? WHERE id=?', [$status,$id]); }
    public function count(): int { return $this->tableCount('purchase_orders'); }
    public function total(): float { return $this->tableTotal('purchase_orders','total_amount'); }
}
