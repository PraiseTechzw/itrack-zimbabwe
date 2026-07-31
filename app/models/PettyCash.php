<?php

require_once dirname(__DIR__) . '/core/Model.php';

class PettyCash extends Model
{
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