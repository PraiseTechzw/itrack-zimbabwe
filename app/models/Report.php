<?php
require_once dirname(__DIR__) . '/models/ModuleModel.php';
class Report extends ModuleModel {
    public function all(): array { return $this->fetchAll("SELECT r.*, u.name AS generated_by_name FROM reports r LEFT JOIN users u ON u.id=r.generated_by ORDER BY r.id DESC"); }
    public function create(array $data): string { $this->execute('INSERT INTO reports (report_name, report_type, filters, generated_by) VALUES (?, ?, ?, ?)', [trim($data['report_name']??''), trim($data['report_type']??'summary'), json_encode($data['filters']??[]), (int)($data['generated_by']??0) ?: null]); return $this->lastInsertId(); }

    public function aiInsightsData(): array
    {
        $today = new DateTimeImmutable('today');
        $currentStart = $today->modify('-29 days');
        $previousStart = $today->modify('-59 days');
        $previousEnd = $today->modify('-30 days');

        $salesByStatus = function (DateTimeImmutable $start, DateTimeImmutable $end): array {
            return $this->fetchAll(
                'SELECT LOWER(status) AS status, COUNT(*) AS count, COALESCE(SUM(total_amount), 0) AS amount FROM sales WHERE sale_date BETWEEN ? AND ? GROUP BY LOWER(status) ORDER BY amount DESC',
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
