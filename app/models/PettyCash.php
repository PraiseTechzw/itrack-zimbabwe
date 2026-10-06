<?php

require_once dirname(__DIR__) . '/core/Model.php';

class PettyCash extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM petty_cash ORDER BY transaction_date DESC, created_at DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM petty_cash WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $this->execute('INSERT INTO petty_cash (user_id, amount, transaction_date, type, description, status) VALUES (:user_id, :amount, :transaction_date, :type, :description, :status)', [
            ':user_id' => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ':amount' => (float) ($data['amount'] ?? 0),
            ':transaction_date' => trim((string) ($data['transaction_date'] ?? date('Y-m-d'))),
            ':type' => trim((string) ($data['type'] ?? 'expense')),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'posted')),
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute('UPDATE petty_cash SET user_id = :user_id, amount = :amount, transaction_date = :transaction_date, type = :type, description = :description, status = :status WHERE id = :id', [
            ':user_id' => !empty($data['user_id']) ? (int) $data['user_id'] : null,
            ':amount' => (float) ($data['amount'] ?? 0),
            ':transaction_date' => trim((string) ($data['transaction_date'] ?? date('Y-m-d'))),
            ':type' => trim((string) ($data['type'] ?? 'expense')),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'posted')),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM petty_cash WHERE id = :id', [':id' => $id]);
    }

    public function countEntries(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM petty_cash')['total'] ?? 0);
    }

    public function totalAmount(): float
    {
        return (float) ($this->fetchOne('SELECT SUM(amount) AS total FROM petty_cash')['total'] ?? 0.0);
    }

    public function recent(int $limit = 5): array
    {
        return $this->fetchAll('SELECT * FROM petty_cash ORDER BY transaction_date DESC LIMIT ' . (int) $limit);
    }
}