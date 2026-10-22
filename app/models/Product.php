<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Product extends Model
{
    public static function itemTypes(): array
    {
        return ['FLS' => ['Escort', 'Dominator'], 'Cameras' => ['FT Cloud', 'IVMS'], 'Sim cards' => ['SIM card', 'Econet', 'NetOne'], 'Tracker' => ['Teltonika FMB920', 'Galileosky 7x', 'Galileosky 10x', 'SmartS-24xx']];
    }
    public static function categories(): array { return array_keys(self::itemTypes()); }
    public static function deviceTypes(): array { return self::itemTypes()['Tracker']; }

    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM products ORDER BY item_category, item_type');
    }

    public function availableForInstallation(string $category, int $includeId = 0): array
    {
        return $this->fetchAll('SELECT * FROM products WHERE item_category = :category AND (status = \'active\' AND opening_stock > 0 OR id = :include_id) ORDER BY item_type', [':category' => $category, ':include_id' => $includeId]);
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM products WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $category = trim((string) ($data['item_category'] ?? 'Tracker'));
        $type = trim((string) ($data['item_type'] ?? $data['device_type'] ?? ''));
        $identifier = trim((string) ($data['identifier'] ?? ''));
        $quantity = max(1, (int) ($data['quantity'] ?? 1));
        $sku = $identifier !== '' ? $identifier : strtoupper(substr($category, 0, 3)) . '-' . strtoupper(bin2hex(random_bytes(5)));
        $sql = 'INSERT INTO products (name, sku, imei, sim_number, device_type, item_category, item_type, category_id, unit, cost_price, selling_price, opening_stock, reorder_level, status, created_at) VALUES (:name, :sku, :imei, NULL, :device_type, :item_category, :item_type, :category_id, :unit, :cost_price, :selling_price, :quantity, 0, :status, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':name' => $type,
            ':sku' => $sku,
            ':imei' => $identifier !== '' ? $identifier : null,
            ':device_type' => $type,
            ':item_category' => $category,
            ':item_type' => $type,
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':quantity' => $quantity,
            ':status' => trim((string) ($data['status'] ?? 'active')),
        ]);
        return $this->lastInsertId();
    }

    public function createMany(array $devices, array $data): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($devices as $device) $this->create(array_merge($data, $device));
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
        $category = trim((string) ($data['item_category'] ?? 'Tracker'));
        $type = trim((string) ($data['item_type'] ?? ''));
        $identifier = trim((string) ($data['identifier'] ?? ''));
        $sql = 'UPDATE products SET name = :name, sku = CASE WHEN :sku_check <> \'\' THEN :sku ELSE sku END, imei = :imei, sim_number = NULL, device_type = :device_type, item_category = :item_category, item_type = :item_type, category_id = :category_id, unit = :unit, cost_price = :cost_price, selling_price = :selling_price, opening_stock = :quantity, status = :status WHERE id = :id';
        $this->execute($sql, [
            ':name' => $type, ':sku_check' => $identifier, ':sku' => $identifier, ':imei' => $identifier !== '' ? $identifier : null,
            ':sim_number' => null, ':device_type' => $type, ':item_category' => $category, ':item_type' => $type,
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':quantity' => max(0, (int) ($data['quantity'] ?? 0)),
            ':status' => trim((string) ($data['status'] ?? 'active')),
            ':id' => $id,
        ]);
    }

    public function consume(int $id, int $quantity = 1): void
    {
        if ($this->execute('UPDATE products SET opening_stock = opening_stock - :qty WHERE id = :id AND opening_stock >= :qty', [':qty' => $quantity, ':id' => $id]) < 1) throw new InvalidArgumentException('The selected inventory item is no longer in stock.');
    }

    public function restore(int $id, int $quantity = 1): void
    {
        if ($id > 0 && $quantity > 0) $this->execute('UPDATE products SET opening_stock = opening_stock + :qty WHERE id = :id', [':qty' => $quantity, ':id' => $id]);
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
