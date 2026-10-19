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
        return $this->fetchAll('SELECT s.id, s.sale_date, s.total_amount, c.company_name AS client_name, c.email AS client_email, c.phone AS client_phone, c.address AS client_address FROM sales s LEFT JOIN clients c ON c.id = s.client_id WHERE NOT EXISTS (SELECT 1 FROM invoices i WHERE i.sale_id = s.id) ORDER BY s.sale_date DESC, s.id DESC');
    }
    public function find(int $id): ?array
    {
        return $this->details($id);
    }
    private function cleanItems(array $items): array
    {
        $clean = [];
        foreach ($items as $item) {
            if (!is_array($item)) continue;
            $description = trim((string) ($item['description'] ?? ''));
            if ($description === '') continue;
            $quantity = max(0.001, (float) ($item['quantity'] ?? 1));
            $unitPrice = max(0, (float) ($item['unit_price'] ?? 0));
            $vatRate = min(100, max(0, (float) ($item['vat_rate'] ?? 0)));
            $excl = round($quantity * $unitPrice, 2);
            $vat = round($excl * $vatRate / 100, 2);
            $clean[] = [
                'quantity' => $quantity,
                'hs_code' => trim((string) ($item['hs_code'] ?? '99001000')),
                'description' => $description,
                'unit_price' => $unitPrice,
                'vat_rate' => $vatRate,
                'vat_amount' => $vat,
                'total_excl' => $excl,
                'total_incl' => round($excl + $vat, 2),
            ];
        }
        if (!$clean) throw new InvalidArgumentException('Add at least one invoice line with a description.');
        return $clean;
    }
    private function saveItems(int $invoiceId, array $items): float
    {
        $this->execute('DELETE FROM invoice_items WHERE invoice_id = ?', [$invoiceId]);
        $total = 0.0;
        foreach ($items as $item) {
            $this->execute('INSERT INTO invoice_items (invoice_id, quantity, hs_code, description, unit_price, vat_rate, vat_amount, total_excl, total_incl) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', [$invoiceId, $item['quantity'], $item['hs_code'], $item['description'], $item['unit_price'], $item['vat_rate'], $item['vat_amount'], $item['total_excl'], $item['total_incl']]);
            $total += $item['total_incl'];
        }
        return round($total, 2);
    }
    public function create(array $data): string
    {
        $saleId = (int) ($data['sale_id'] ?? 0);
        $sale = $this->fetchOne('SELECT s.id, s.total_amount, c.company_name, c.email, c.phone, c.address FROM sales s LEFT JOIN clients c ON c.id=s.client_id WHERE s.id = :id', [':id' => $saleId]);
        if (!$sale) throw new InvalidArgumentException('Select a valid sale for this invoice.');
        $items = $this->cleanItems($data['items'] ?? []);
        $currency = strtoupper(trim((string) ($data['currency'] ?? 'USD')));
        if (!in_array($currency, ['USD', 'ZWG'], true)) $currency = 'USD';
        $invoiceNumber = 'INV S/' . date('Y') . '/' . strtoupper(bin2hex(random_bytes(3)));
        $customerName = trim((string) ($data['customer_registered_name'] ?? '')) ?: (string) ($sale['company_name'] ?? '');
        $this->db->beginTransaction();
        try {
            $this->execute('INSERT INTO invoices (sale_id, invoice_number, due_date, status, total_amount, currency, issue_date, reference_number, sales_person, customer_registered_name, customer_trade_name, customer_address, customer_email, customer_phone, customer_tin, customer_vat, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)', [
                $saleId, $invoiceNumber, trim((string) ($data['due_date'] ?? date('Y-m-d'))), trim((string) ($data['status'] ?? 'unpaid')), 0, $currency, trim((string) ($data['issue_date'] ?? date('Y-m-d'))), trim((string) ($data['reference_number'] ?? $invoiceNumber)), trim((string) ($data['sales_person'] ?? '')), $customerName, trim((string) ($data['customer_trade_name'] ?? $customerName)), trim((string) ($data['customer_address'] ?? $sale['address'] ?? '')), trim((string) ($data['customer_email'] ?? $sale['email'] ?? '')), trim((string) ($data['customer_phone'] ?? $sale['phone'] ?? '')), trim((string) ($data['customer_tin'] ?? '')), trim((string) ($data['customer_vat'] ?? '')),
            ]);
            $id = (int) $this->lastInsertId();
            $total = $this->saveItems($id, $items);
            $this->execute('UPDATE invoices SET total_amount = ? WHERE id = ?', [$total, $id]);
            $this->db->commit();
            return (string) $id;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
    public function update(int $id, array $data): void
    {
        $items = $this->cleanItems($data['items'] ?? []);
        $currency = strtoupper(trim((string) ($data['currency'] ?? 'USD')));
        if (!in_array($currency, ['USD', 'ZWG'], true)) $currency = 'USD';
        $this->db->beginTransaction();
        try {
            $this->execute('UPDATE invoices SET due_date=?, status=?, currency=?, issue_date=?, reference_number=?, sales_person=?, customer_registered_name=?, customer_trade_name=?, customer_address=?, customer_email=?, customer_phone=?, customer_tin=?, customer_vat=? WHERE id=?', [trim((string) ($data['due_date'] ?? date('Y-m-d'))), trim((string) ($data['status'] ?? 'unpaid')), $currency, trim((string) ($data['issue_date'] ?? date('Y-m-d'))), trim((string) ($data['reference_number'] ?? '')), trim((string) ($data['sales_person'] ?? '')), trim((string) ($data['customer_registered_name'] ?? '')), trim((string) ($data['customer_trade_name'] ?? '')), trim((string) ($data['customer_address'] ?? '')), trim((string) ($data['customer_email'] ?? '')), trim((string) ($data['customer_phone'] ?? '')), trim((string) ($data['customer_tin'] ?? '')), trim((string) ($data['customer_vat'] ?? '')), $id]);
            $total = $this->saveItems($id, $items);
            $this->execute('UPDATE invoices SET total_amount=? WHERE id=?', [$total, $id]);
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }
    public function details(int $id): ?array
    {
        $invoice = $this->fetchOne('SELECT i.*, s.sale_date, c.company_name AS client_name FROM invoices i JOIN sales s ON s.id=i.sale_id LEFT JOIN clients c ON c.id=s.client_id WHERE i.id = :id', [':id' => $id]);
        if (!$invoice) return null;
        $invoice['items'] = $this->fetchAll('SELECT * FROM invoice_items WHERE invoice_id=:id ORDER BY id', [':id' => $id]);
        if (!$invoice['items']) {
            $legacyItems = $this->fetchAll('SELECT CASE WHEN p.imei IS NULL THEN p.sku ELSE NULL END AS hs_code, p.name AS description, si.quantity, si.unit_price, si.total_price FROM sale_items si JOIN products p ON p.id=si.product_id WHERE si.sale_id=:sale_id ORDER BY si.id', [':sale_id' => (int) $invoice['sale_id']]);
            foreach ($legacyItems as $line) {
                $line['vat_rate'] = 0;
                $line['vat_amount'] = 0;
                $line['total_excl'] = (float) $line['total_price'];
                $line['total_incl'] = (float) $line['total_price'];
                $invoice['items'][] = $line;
            }
        }
        $invoice['subtotal'] = array_sum(array_column($invoice['items'], 'total_excl'));
        $invoice['vat_total'] = array_sum(array_column($invoice['items'], 'vat_amount'));
        if (!$invoice['items']) {
            $invoice['items'] = [['quantity'=>1, 'hs_code'=>'', 'description'=>'Services', 'unit_price'=>(float)$invoice['total_amount'], 'vat_rate'=>0, 'vat_amount'=>0, 'total_excl'=>(float)$invoice['total_amount'], 'total_incl'=>(float)$invoice['total_amount']]];
            $invoice['subtotal'] = (float) $invoice['total_amount'];
            $invoice['vat_total'] = 0;
        }
        return $invoice;
    }
    public function delete(int $id): void { $this->execute('DELETE FROM invoices WHERE id = :id', [':id' => $id]); }
    public function countInvoices(): int { return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM invoices')['total'] ?? 0); }
    public function totalAmount(): float { return (float) ($this->fetchOne('SELECT SUM(total_amount) AS total FROM invoices')['total'] ?? 0.0); }
    public function totalsByCurrency(): array
    {
        return $this->fetchAll("SELECT UPPER(COALESCE(NULLIF(currency, ''), 'USD')) AS currency, SUM(total_amount) AS total FROM invoices GROUP BY UPPER(COALESCE(NULLIF(currency, ''), 'USD')) ORDER BY currency");
    }
    public function recent(int $limit = 5): array { return $this->fetchAll('SELECT * FROM invoices ORDER BY created_at DESC LIMIT ' . (int) $limit); }
}
