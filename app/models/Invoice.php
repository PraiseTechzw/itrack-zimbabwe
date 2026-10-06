<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Invoice extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT i.*, c.company_name AS client_name FROM invoices i JOIN sales s ON s.id = i.sale_id LEFT JOIN clients c ON c.id = s.client_id ORDER BY i.created_at DESC');
    }

    public function availableSales(): array
    {
        return $this->fetchAll('SELECT s.id, s.sale_date, s.total_amount, c.company_name AS client_name FROM sales s LEFT JOIN clients c ON c.id = s.client_id WHERE NOT EXISTS (SELECT 1 FROM invoices i WHERE i.sale_id = s.id) ORDER BY s.sale_date DESC, s.id DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM invoices WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $saleId = (int) ($data['sale_id'] ?? 0);
        $sale = $this->fetchOne('SELECT total_amount FROM sales WHERE id = :id', [':id' => $saleId]);
        if (!$sale) {
            throw new InvalidArgumentException('Select a valid sale for this invoice.');
        }

        $sql = 'INSERT INTO invoices (sale_id, invoice_number, due_date, status, total_amount, created_at) VALUES (:sale_id, :invoice_number, :due_date, :status, :total_amount, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':sale_id' => $saleId,
            ':invoice_number' => 'INV-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3))),
            ':due_date' => trim((string) ($data['due_date'] ?? date('Y-m-d'))),
            ':status' => 'unpaid',
            ':total_amount' => (float) $sale['total_amount'],
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->execute('UPDATE invoices SET due_date = :due_date, status = :status WHERE id = :id', [
            ':due_date' => trim((string) ($data['due_date'] ?? date('Y-m-d'))),
            ':status' => trim((string) ($data['status'] ?? 'unpaid')),
            ':id' => $id,
        ]);
    }

    public function details(int $id): ?array
    {
        $invoice = $this->fetchOne('SELECT i.*, s.sale_date, c.company_name AS client_name, c.contact_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address FROM invoices i JOIN sales s ON s.id = i.sale_id LEFT JOIN clients c ON c.id = s.client_id WHERE i.id = :id', [':id' => $id]);
        if (!$invoice) {
            return null;
        }

        $invoice['items'] = $this->fetchAll('SELECT p.name AS product_name, p.sku, p.unit, si.quantity, si.unit_price, si.total_price FROM sale_items si JOIN products p ON p.id = si.product_id WHERE si.sale_id = :sale_id ORDER BY si.id', [':sale_id' => (int) $invoice['sale_id']]);
        $invoice['payments'] = $this->fetchAll('SELECT payment_date, amount, method, reference FROM payments WHERE invoice_id = :invoice_id ORDER BY payment_date, id', [':invoice_id' => $id]);
        $invoice['paid_amount'] = array_sum(array_map(static fn (array $payment): float => (float) $payment['amount'], $invoice['payments']));
        $invoice['balance_due'] = max(0, (float) $invoice['total_amount'] - $invoice['paid_amount']);

        return $invoice;
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
