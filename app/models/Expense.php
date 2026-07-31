<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Expense extends Model
{
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