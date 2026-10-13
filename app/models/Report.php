<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Report extends ModuleModel {
    public function all(): array { return $this->fetchAll("SELECT r.*, u.name AS generated_by_name FROM reports r LEFT JOIN users u ON u.id=r.generated_by ORDER BY r.id DESC"); }
    public function create(array $data): string { $this->execute('INSERT INTO reports (report_name, report_type, filters, generated_by) VALUES (?, ?, ?, ?)', [trim($data['report_name']??''), trim($data['report_type']??'summary'), json_encode($data['filters']??[]), (int)($data['generated_by']??0) ?: null]); return $this->lastInsertId(); }

    private function periodBounds(string $period): ?array
    {
        $today = new DateTimeImmutable('today');
        return match ($period) {
            'today' => [$today, $today->modify('+1 day')],
            'last_7_days' => [$today->modify('-6 days'), $today->modify('+1 day')],
            'last_30_days' => [$today->modify('-29 days'), $today->modify('+1 day')],
            'this_month' => [$today->modify('first day of this month'), $today->modify('first day of next month')],
            default => null,
        };
    }

    public function dateBounds(string $type): ?array
    {
        $source = match ($type) {
            'Receivables' => ['invoices', 'due_date'],
            'GPS activity' => ['gps_devices', 'installed_at'],
            'Cash book' => ['cash_book', 'entry_date'],
            'Inventory valuation' => null,
            default => ['sales', 'sale_date'],
        };
        if ($source === null) {
            return null;
        }
        [$table, $column] = $source;

        $dates = $this->fetchOne('SELECT MIN(' . $column . ') AS first_date, MAX(' . $column . ') AS last_date FROM ' . $table);
        $firstDate = $dates['first_date'] ?? null;
        $lastDate = $dates['last_date'] ?? null;
        return [
            'min' => $firstDate === null || $firstDate === '' ? null : substr((string) $firstDate, 0, 10),
            'max' => $lastDate === null || $lastDate === '' ? null : substr((string) $lastDate, 0, 10),
        ];
    }

    private function periodCondition(string $column, string $period, ?string $startDate = null, ?string $endDate = null): array
    {
        if ($period === 'custom' && $startDate !== null && $endDate !== null) {
            $endExclusive = (new DateTimeImmutable($endDate))->modify('+1 day')->format('Y-m-d');
            return [
                ' WHERE ' . $column . ' >= ? AND ' . $column . ' < ?',
                [$startDate, $endExclusive],
            ];
        }

        $bounds = $this->periodBounds($period);
        if ($bounds === null) {
            return ['', []];
        }
        return [
            ' WHERE ' . $column . ' >= ? AND ' . $column . ' < ?',
            [$bounds[0]->format('Y-m-d'), $bounds[1]->format('Y-m-d')],
        ];
    }

    public function exportRows(string $type, string $period = 'all', ?string $startDate = null, ?string $endDate = null): array
    {
        [$condition, $params] = match ($type) {
            'Inventory valuation' => ['', []],
            'Receivables' => $this->periodCondition('i.due_date', $period, $startDate, $endDate),
            'GPS activity' => $this->periodCondition('installed_at', $period, $startDate, $endDate),
            'Cash book' => $this->periodCondition('entry_date', $period, $startDate, $endDate),
            default => $this->periodCondition('s.sale_date', $period, $startDate, $endDate),
        };
        return match ($type) {
            'Inventory valuation' => $this->fetchAll('SELECT p.name, p.sku, COALESCE(SUM(s.quantity),0) AS quantity_on_hand, p.cost_price, COALESCE(SUM(s.quantity),0)*p.cost_price AS stock_value FROM products p LEFT JOIN stocks s ON s.product_id=p.id GROUP BY p.id ORDER BY p.name'),
            'Receivables' => $this->fetchAll('SELECT i.invoice_number, c.company_name AS client, i.due_date, i.status, i.total_amount FROM invoices i LEFT JOIN sales s ON s.id=i.sale_id LEFT JOIN clients c ON c.id=s.client_id' . $condition . ' ORDER BY i.due_date', $params),
            'GPS activity' => $this->fetchAll('SELECT device_name, device_type, imei, serial, status, installed_at FROM gps_devices' . $condition . ' ORDER BY device_name', $params),
            'Cash book' => $this->fetchAll('SELECT entry_date, description, debit, credit, balance FROM cash_book' . $condition . ' ORDER BY entry_date DESC', $params),
            default => $this->fetchAll('SELECT s.sale_date, c.company_name AS client, s.sale_type, s.package_type, s.status, s.total_amount FROM sales s LEFT JOIN clients c ON c.id=s.client_id' . $condition . ' ORDER BY s.sale_date DESC', $params),
        };
    }
    public function aiInsightsData(): array
    {
        $today = new DateTimeImmutable('today');
        $currentStart = $today->modify('-29 days');
        $previousStart = $today->modify('-59 days');
        $previousEnd = $today->modify('-30 days');

        $salesByStatus = function (DateTimeImmutable $start, DateTimeImmutable $end): array {
            return $this->fetchAll(
                'SELECT LOWER(status) AS status, LOWER(sale_type) AS sale_type, LOWER(package_type) AS package_type, COUNT(*) AS count, COALESCE(SUM(total_amount), 0) AS amount FROM sales WHERE sale_date BETWEEN ? AND ? GROUP BY LOWER(status), LOWER(sale_type), LOWER(package_type) ORDER BY amount DESC',
                [$start->format('Y-m-d'), $end->format('Y-m-d')]
            );
        };

        return [
            'periods' => [
                'recent_30_days' => [
                    'from' => $currentStart->format('Y-m-d'),
                    'to' => $today->format('Y-m-d'),
                    'sales_by_status' => $salesByStatus($currentStart, $today),
                ],
                'previous_30_days' => [
                    'from' => $previousStart->format('Y-m-d'),
                    'to' => $previousEnd->format('Y-m-d'),
                    'sales_by_status' => $salesByStatus($previousStart, $previousEnd),
                ],
            ],
            'low_stock_products' => $this->fetchAll(
                'SELECT p.name, p.sku, COALESCE(SUM(s.quantity), 0) AS quantity_on_hand, MAX(p.reorder_level) AS reorder_level FROM products p LEFT JOIN stocks s ON s.product_id = p.id WHERE p.status = ? AND p.reorder_level > 0 GROUP BY p.name, p.sku HAVING COALESCE(SUM(s.quantity), 0) <= MAX(p.reorder_level) ORDER BY quantity_on_hand ASC LIMIT 8',
                ['active']
            ),
            'purchase_orders_by_status' => $this->fetchAll(
                'SELECT LOWER(status) AS status, COUNT(*) AS count, COALESCE(SUM(total_amount), 0) AS amount FROM purchase_orders GROUP BY LOWER(status) ORDER BY count DESC LIMIT 8'
            ),
            'currency_note' => 'Amounts use the currency configured by the business; do not infer or convert currency.',
        ];
    }
}
