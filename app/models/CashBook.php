<?php

require_once dirname(__DIR__) . '/core/Model.php';

class CashBook extends Model
{
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