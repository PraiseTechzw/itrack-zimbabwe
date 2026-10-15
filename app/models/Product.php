<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Product extends Model
{
    public static function deviceTypes(): array
    {
        return ['Galileosky 7x', 'Teltonika FMB920', 'Smart S-24xx'];
    }

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
        $sql = 'INSERT INTO products (name, sku, imei, device_type, category_id, unit, cost_price, selling_price, opening_stock, reorder_level, status, created_at) VALUES (:name, :sku, :imei, :device_type, :category_id, :unit, :cost_price, :selling_price, 1, 0, :status, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':name' => trim((string) ($data['device_type'] ?? self::deviceTypes()[0])),
            ':sku' => trim((string) ($data['imei'] ?? '')),
            ':imei' => trim((string) ($data['imei'] ?? '')),
            ':device_type' => trim((string) ($data['device_type'] ?? self::deviceTypes()[0])),
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':status' => trim((string) ($data['status'] ?? 'active')),
        ]);
        return $this->lastInsertId();
    }

    public function createMany(array $imeis, array $data): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($imeis as $imei) {
                $data['imei'] = $imei;
                $this->create($data);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE products SET name = :name, sku = CASE WHEN imei IS NOT NULL THEN :sku ELSE sku END, imei = :imei, device_type = :device_type, category_id = :category_id, unit = :unit, cost_price = :cost_price, selling_price = :selling_price, status = :status WHERE id = :id';
        $this->execute($sql, [
            ':name' => trim((string) ($data['device_type'] ?? self::deviceTypes()[0])),
            ':sku' => trim((string) ($data['imei'] ?? '')),
            ':imei' => trim((string) ($data['imei'] ?? '')),
            ':device_type' => trim((string) ($data['device_type'] ?? self::deviceTypes()[0])),
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':status' => trim((string) ($data['status'] ?? 'active')),
            ':id' => $id,
        ]);
    }

    public function imeiExists(string $imei, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM products WHERE (imei = :imei OR sku = :sku)';
        $params = [':imei' => trim($imei), ':sku' => trim($imei)];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        return $this->fetchOne($sql, $params) !== null;
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
