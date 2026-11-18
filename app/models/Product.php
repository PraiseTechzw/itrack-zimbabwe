<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Product extends Model
{
    public static function itemTypes(): array
    {
        return [
            'FLS' => ['Escort', 'Dominator'],
            'Cameras' => ['FT Cloud', 'IVMS'],
            'Hard drives' => ['Hard drive'],
            'Sim cards' => ['SIM card', 'Econet', 'NetOne'],
            'Tracker' => ['Teltonika FMB920', 'Galileosky 7x', 'Galileosky 10x', 'SmartS-24xx'],
        ];
    }
    public static function categories(): array { return array_keys(self::itemTypes()); }
    public static function deviceTypes(): array { return self::itemTypes()['Tracker']; }

    /**
     * Serialised categories require a unique identifier. SIM lines are limited
     * to 10 digits; hard drives use an alphanumeric serial; everything else
     * (FLS, cameras, trackers) uses digits only.
     */
    public static function identifierRules(string $category): array
    {
        return match ($category) {
            'Sim cards' => ['required' => true, 'pattern' => '/^\d{1,10}$/', 'hint' => 'A unique SIM line number of up to 10 digits is required.'],
            'Hard drives' => ['required' => true, 'pattern' => '/^[A-Za-z0-9][A-Za-z0-9-]{0,99}$/', 'hint' => 'A unique hard drive serial (letters and numbers) is required.'],
            'FLS', 'Cameras' => ['required' => true, 'pattern' => '/^\d{1,100}$/', 'hint' => 'A unique numeric serial is required for FLS and camera items.'],
            default => ['required' => false, 'pattern' => '/^\d{0,100}$/', 'hint' => 'Optional for tracker stock. Enter a tracker IMEI only when this row represents one serialized device.'],
        };
    }

    /** Shared server-side identifier validation for create, bulk import and edit. */
    public static function identifierError(string $category, string $identifier): ?string
    {
        $identifier = trim($identifier);
        $rules = self::identifierRules($category);
        if ($identifier === '') {
            return $rules['required'] ? 'Enter a unique identifier for this item.' : null;
        }
        if (!preg_match($rules['pattern'], $identifier)) {
            return $category === 'Sim cards'
                ? 'A SIM line number must be digits only and at most 10 digits.'
                : ($category === 'Hard drives'
                    ? 'A hard drive serial must use letters or numbers only (maximum 100 characters).'
                    : 'Use digits only for this serial / IMEI (maximum 100 digits).');
        }
        return null;
    }

    public static function vatRate(mixed $value): float
    {
        if ($value === null || $value === '') return 15.0;
        if (!is_numeric($value)) throw new InvalidArgumentException('Enter a valid VAT rate.');
        $rate = (float) $value;
        if ($rate < 0 || $rate > 100) throw new InvalidArgumentException('VAT rate must be between 0 and 100.');
        return round($rate, 3);
    }

    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM products ORDER BY item_category, item_type');
    }

    public function availableForInstallation(string $category, int $includeId = 0, bool $requireIdentifier = false): array
    {
        $identifierClause = $requireIdentifier ? " AND imei IS NOT NULL AND TRIM(imei) <> ''" : '';
        return $this->fetchAll('SELECT * FROM products WHERE item_category = :category AND ((status = \'active\' AND opening_stock > 0' . $identifierClause . ') OR id = :include_id) ORDER BY item_type', [':category' => $category, ':include_id' => $includeId]);
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
        $sql = 'INSERT INTO products (name, sku, imei, sim_number, device_type, item_category, item_type, category_id, unit, cost_price, selling_price, vat_rate, opening_stock, reorder_level, status, created_at) VALUES (:name, :sku, :imei, NULL, :device_type, :item_category, :item_type, :category_id, :unit, :cost_price, :selling_price, :vat_rate, :quantity, :reorder_level, :status, CURRENT_TIMESTAMP)';
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
            ':vat_rate' => self::vatRate($data['vat_rate'] ?? null),
            ':quantity' => $quantity,
            ':reorder_level' => max(0, (int) ($data['reorder_level'] ?? 0)),
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
        $sql = 'UPDATE products SET name = :name, sku = CASE WHEN :sku_check <> \'\' THEN :sku ELSE sku END, imei = :imei, sim_number = NULL, device_type = :device_type, item_category = :item_category, item_type = :item_type, category_id = :category_id, unit = :unit, cost_price = :cost_price, selling_price = :selling_price, vat_rate = :vat_rate, opening_stock = :quantity, reorder_level = :reorder_level, status = :status WHERE id = :id';
        $this->execute($sql, [
            ':name' => $type, ':sku_check' => $identifier, ':sku' => $identifier, ':imei' => $identifier !== '' ? $identifier : null,
            ':device_type' => $type, ':item_category' => $category, ':item_type' => $type,
            ':category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : null,
            ':unit' => trim((string) ($data['unit'] ?? 'pcs')),
            ':cost_price' => (float) ($data['cost_price'] ?? 0),
            ':selling_price' => (float) ($data['selling_price'] ?? 0),
            ':vat_rate' => self::vatRate($data['vat_rate'] ?? null),
            ':quantity' => max(0, (int) ($data['quantity'] ?? 0)),
            ':reorder_level' => max(0, (int) ($data['reorder_level'] ?? 0)),
            ':status' => trim((string) ($data['status'] ?? 'active')),
            ':id' => $id,
        ]);
    }

    public function consume(int $id, int $quantity = 1): void
    {
        if ($this->execute('UPDATE products SET opening_stock = opening_stock - :quantity_to_consume WHERE id = :id AND opening_stock >= :minimum_quantity', [':quantity_to_consume' => $quantity, ':minimum_quantity' => $quantity, ':id' => $id]) < 1) throw new InvalidArgumentException('The selected inventory item is no longer in stock.');
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

    /**
     * An item's own reorder level wins when set; otherwise the configured
     * default applies. Only active items are considered.
     */
    public function lowStockItems(int $defaultThreshold = 0, int $limit = 20): array
    {
        return $this->fetchAll(
            'SELECT id, name, sku, imei, item_category, item_type, opening_stock,
                    CASE WHEN reorder_level > 0 THEN reorder_level ELSE ? END AS reorder_threshold,
                    CASE WHEN reorder_level > 0 THEN 1 ELSE 0 END AS has_own_reorder_level
             FROM products
             WHERE status = \'active\' AND opening_stock <= CASE WHEN reorder_level > 0 THEN reorder_level ELSE ? END
             ORDER BY opening_stock ASC, name ASC LIMIT ' . max(1, $limit),
            [$defaultThreshold, $defaultThreshold]
        );
    }

    public function lowStockCount(int $defaultThreshold = 0): int
    {
        return (int) ($this->fetchOne(
            'SELECT COUNT(*) AS total FROM products WHERE status = \'active\' AND opening_stock <= CASE WHEN reorder_level > 0 THEN reorder_level ELSE ? END',
            [$defaultThreshold]
        )['total'] ?? 0);
    }

    public function inventoryValue(): float
    {
        $row = $this->fetchOne('SELECT COALESCE(SUM(opening_stock * cost_price), 0) AS value FROM products');
        return (float) ($row['value'] ?? 0);
    }

    /**
     * Units currently out on active installations, keyed by product id.
     * One installation consumes one unit of each selected product, so unpivoting
     * the equipment foreign keys gives the installed quantity per product.
     */
    public function installedQuantities(): array
    {
        $rows = $this->fetchAll(
            'SELECT product_id, COUNT(*) AS quantity_installed FROM (
                SELECT tracker_product_id AS product_id FROM gps_devices WHERE status = :status AND tracker_product_id IS NOT NULL
                UNION ALL SELECT fls_product_id FROM gps_devices WHERE status = :status AND fls_product_id IS NOT NULL
                UNION ALL SELECT camera_product_id FROM gps_devices WHERE status = :status AND camera_product_id IS NOT NULL
                UNION ALL SELECT sim_product_id FROM gps_devices WHERE status = :status AND sim_product_id IS NOT NULL
                UNION ALL SELECT camera_sim_product_id FROM gps_devices WHERE status = :status AND camera_sim_product_id IS NOT NULL
                UNION ALL SELECT hard_drive_product_id FROM gps_devices WHERE status = :status AND hard_drive_product_id IS NOT NULL
            ) installed GROUP BY product_id',
            [':status' => 'installed']
        );
        $quantities = [];
        foreach ($rows as $row) {
            $quantities[(int) $row['product_id']] = (int) $row['quantity_installed'];
        }
        return $quantities;
    }
}
