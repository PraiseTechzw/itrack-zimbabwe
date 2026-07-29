<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Product extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM products ORDER BY name ASC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM products WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO products (name, sku, category_id, unit, cost_price, selling_price, opening_stock, reorder_level, status, created_at) VALUES (:name, :sku, :category_id, :unit, :cost_price, :selling_price, :opening_stock, :reorder_level, :status, NOW())';
        $this->execute($sql, [
            ':name' => trim((string) ($data['name'] ?? '')),
            ':sku' => strtoupper(trim((string) ($data['sku'] ?? ''))),
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':opening_stock' => (int) ($data['opening_stock'] ?? 0),
            ':reorder_level' => (int) ($data['reorder_level'] ?? 0),
            ':status' => trim((string) ($data['status'] ?? 'active')),
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE products SET name = :name, sku = :sku, category_id = :category_id, unit = :unit, cost_price = :cost_price, selling_price = :selling_price, opening_stock = :opening_stock, reorder_level = :reorder_level, status = :status WHERE id = :id';
        $this->execute($sql, [
            ':name' => trim((string) ($data['name'] ?? '')),
            ':sku' => strtoupper(trim((string) ($data['sku'] ?? ''))),
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':opening_stock' => (int) ($data['opening_stock'] ?? 0),
            ':reorder_level' => (int) ($data['reorder_level'] ?? 0),
            ':status' => trim((string) ($data['status'] ?? 'active')),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM products WHERE id = :id', [':id' => $id]);
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
