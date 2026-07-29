<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Product extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM products ORDER BY name ASC');
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO products (name, sku, category_id, unit, cost_price, selling_price, opening_stock, reorder_level, status, created_at) VALUES (:name, :sku, :category_id, :unit, :cost_price, :selling_price, :opening_stock, :reorder_level, :status, NOW())';
        $this->execute($sql, [
            ':name' => $data['name'],
            ':sku' => $data['sku'],
            ':category_id' => $data['category_id'] ?? null,
            ':unit' => $data['unit'] ?? 'pcs',
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':opening_stock' => (int) ($data['opening_stock'] ?? 0),
            ':reorder_level' => (int) ($data['reorder_level'] ?? 0),
            ':status' => $data['status'] ?? 'active',
        ]);
        return $this->lastInsertId();
    }

    public function countProducts(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM products')['total'] ?? 0);
    }

    public function lowStockCount(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM products WHERE opening_stock <= reorder_level')['total'] ?? 0);
    }

    public function inventoryValue(): float
    {
        $row = $this->fetchOne('SELECT COALESCE(SUM(opening_stock * cost_price), 0) AS value FROM products');
        return (float) ($row['value'] ?? 0);
    }
}
