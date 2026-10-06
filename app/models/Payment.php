<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Payment extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM payments ORDER BY payment_date DESC, created_at DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM payments WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $this->execute('INSERT INTO payments (invoice_id, sale_id, paid_by, payment_date, amount, method, reference) VALUES (:invoice_id, :sale_id, :paid_by, :payment_date, :amount, :method, :reference)', [
            ':invoice_id' => !empty($data['invoice_id']) ? (int) $data['invoice_id'] : null,
            ':sale_id' => !empty($data['sale_id']) ? (int) $data['sale_id'] : null,
            ':paid_by' => !empty($data['paid_by']) ? (int) $data['paid_by'] : null,
            ':payment_date' => trim((string) ($data['payment_date'] ?? date('Y-m-d'))),
            ':amount' => (float) ($data['amount'] ?? 0),
            ':method' => trim((string) ($data['method'] ?? 'cash')),
            ':reference' => trim((string) ($data['reference'] ?? '')),
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute('UPDATE payments SET invoice_id = :invoice_id, sale_id = :sale_id, paid_by = :paid_by, payment_date = :payment_date, amount = :amount, method = :method, reference = :reference WHERE id = :id', [
            ':invoice_id' => !empty($data['invoice_id']) ? (int) $data['invoice_id'] : null,
            ':sale_id' => !empty($data['sale_id']) ? (int) $data['sale_id'] : null,
            ':paid_by' => !empty($data['paid_by']) ? (int) $data['paid_by'] : null,
            ':payment_date' => trim((string) ($data['payment_date'] ?? date('Y-m-d'))),
            ':amount' => (float) ($data['amount'] ?? 0),
            ':method' => trim((string) ($data['method'] ?? 'cash')),
            ':reference' => trim((string) ($data['reference'] ?? '')),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM payments WHERE id = :id', [':id' => $id]);
    }

    public function countPayments(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM payments')['total'] ?? 0);
    }

    public function totalAmount(): float
    {
        return (float) ($this->fetchOne('SELECT SUM(amount) AS total FROM payments')['total'] ?? 0.0);
    }

    public function recent(int $limit = 5): array
    {
        return $this->fetchAll('SELECT * FROM payments ORDER BY payment_date DESC LIMIT ' . (int) $limit);
    }
}
