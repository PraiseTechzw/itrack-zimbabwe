<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Invoice extends Model
{
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
