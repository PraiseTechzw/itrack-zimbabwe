<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Expense extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM expenses ORDER BY payment_date DESC, created_at DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM expenses WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $this->execute('INSERT INTO expenses (category, amount, paid_to, paid_by, payment_date, description) VALUES (:category, :amount, :paid_to, :paid_by, :payment_date, :description)', [
            ':category' => trim((string) ($data['category'] ?? '')),
            ':amount' => (float) ($data['amount'] ?? 0),
            ':paid_to' => trim((string) ($data['paid_to'] ?? '')),
            ':paid_by' => !empty($data['paid_by']) ? (int) $data['paid_by'] : null,
            ':payment_date' => trim((string) ($data['payment_date'] ?? date('Y-m-d'))),
            ':description' => trim((string) ($data['description'] ?? '')),
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute('UPDATE expenses SET category = :category, amount = :amount, paid_to = :paid_to, paid_by = :paid_by, payment_date = :payment_date, description = :description WHERE id = :id', [
            ':category' => trim((string) ($data['category'] ?? '')),
            ':amount' => (float) ($data['amount'] ?? 0),
            ':paid_to' => trim((string) ($data['paid_to'] ?? '')),
            ':paid_by' => !empty($data['paid_by']) ? (int) $data['paid_by'] : null,
            ':payment_date' => trim((string) ($data['payment_date'] ?? date('Y-m-d'))),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM expenses WHERE id = :id', [':id' => $id]);
    }

    public function countExpenses(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM expenses')['total'] ?? 0);
    }

    public function totalAmount(): float
    {
        return (float) ($this->fetchOne('SELECT SUM(amount) AS total FROM expenses')['total'] ?? 0.0);
    }

    public function recent(int $limit = 5): array
    {
        return $this->fetchAll('SELECT * FROM expenses ORDER BY payment_date DESC LIMIT ' . (int) $limit);
    }
}