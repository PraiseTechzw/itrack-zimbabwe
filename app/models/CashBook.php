<?php

require_once dirname(__DIR__) . '/core/Model.php';

class CashBook extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM cash_book ORDER BY entry_date DESC, created_at DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM cash_book WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $this->execute('INSERT INTO cash_book (entry_date, description, debit, credit, balance, created_by) VALUES (:entry_date, :description, :debit, :credit, :balance, :created_by)', [
            ':entry_date' => trim((string) ($data['entry_date'] ?? date('Y-m-d'))),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':debit' => (float) ($data['debit'] ?? 0),
            ':credit' => (float) ($data['credit'] ?? 0),
            ':balance' => (float) ($data['balance'] ?? 0),
            ':created_by' => !empty($data['created_by']) ? (int) $data['created_by'] : null,
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute('UPDATE cash_book SET entry_date = :entry_date, description = :description, debit = :debit, credit = :credit, balance = :balance, created_by = :created_by WHERE id = :id', [
            ':entry_date' => trim((string) ($data['entry_date'] ?? date('Y-m-d'))),
            ':description' => trim((string) ($data['description'] ?? '')),
            ':debit' => (float) ($data['debit'] ?? 0),
            ':credit' => (float) ($data['credit'] ?? 0),
            ':balance' => (float) ($data['balance'] ?? 0),
            ':created_by' => !empty($data['created_by']) ? (int) $data['created_by'] : null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM cash_book WHERE id = :id', [':id' => $id]);
    }

    public function lastBalance(): float
    {
        $row = $this->fetchOne('SELECT balance FROM cash_book ORDER BY created_at DESC LIMIT 1');
        return (float) ($row['balance'] ?? 0.0);
    }

    public function recent(int $limit = 5): array
    {
        return $this->fetchAll('SELECT * FROM cash_book ORDER BY created_at DESC LIMIT ' . (int) $limit);
    }
}