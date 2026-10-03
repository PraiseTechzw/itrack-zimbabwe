<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Payment extends Model
{
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
