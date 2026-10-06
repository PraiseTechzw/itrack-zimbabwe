<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Invoice extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM invoices ORDER BY created_at DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM invoices WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO invoices (sale_id, invoice_number, due_date, status, total_amount, created_at) VALUES (:sale_id, :invoice_number, :due_date, :status, :total_amount, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':sale_id' => !empty($data['sale_id']) ? (int) $data['sale_id'] : 0,
            ':invoice_number' => trim((string) ($data['invoice_number'] ?? '')),
            ':due_date' => trim((string) ($data['due_date'] ?? date('Y-m-d'))),
            ':status' => trim((string) ($data['status'] ?? 'unpaid')),
            ':total_amount' => (float) ($data['total_amount'] ?? 0),
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute('UPDATE invoices SET sale_id = :sale_id, invoice_number = :invoice_number, due_date = :due_date, status = :status, total_amount = :total_amount WHERE id = :id', [
            ':sale_id' => !empty($data['sale_id']) ? (int) $data['sale_id'] : 0,
            ':invoice_number' => trim((string) ($data['invoice_number'] ?? '')),
            ':due_date' => trim((string) ($data['due_date'] ?? date('Y-m-d'))),
            ':status' => trim((string) ($data['status'] ?? 'unpaid')),
            ':total_amount' => (float) ($data['total_amount'] ?? 0),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM invoices WHERE id = :id', [':id' => $id]);
    }

    public function countInvoices(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM invoices')['total'] ?? 0);
    }

    public function totalAmount(): float
    {
        return (float) ($this->fetchOne('SELECT SUM(total_amount) AS total FROM invoices')['total'] ?? 0.0);
    }

    public function recent(int $limit = 5): array
    {
        return $this->fetchAll('SELECT * FROM invoices ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }
}
