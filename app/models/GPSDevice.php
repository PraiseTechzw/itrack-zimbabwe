<?php

require_once dirname(__DIR__) . '/core/Model.php';
require_once __DIR__ . '/Product.php';

class GPSDevice extends Model
{
    public static function deviceTypes(): array
    {
        return ['Galileosky 7x', 'Teltonika FMB920', 'Smart S-24xx', 'Teltonika FMC920', 'Teltonika FMB140', 'Teltonika FMC130', 'Queclink GV300W', 'Concox GT06N', 'Ruptela Trace5', 'Howen Hero-ME40'];
    }
    public static function packageTypes(): array
    {
        return ['Basic', 'Basic + FLS', 'Basic + FLS + Camera'];
    }
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM gps_devices ORDER BY device_name ASC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM gps_devices WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $trackerId = (int) ($data['tracker_product_id'] ?? 0);
        $flsId = (int) ($data['fls_product_id'] ?? 0);
        $cameraId = (int) ($data['camera_product_id'] ?? 0);
        $simId = (int) ($data['sim_product_id'] ?? 0);
        $products = new Product();
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) $this->db->beginTransaction();
        try {
        self::validatePackageSelection($data, $trackerId, $flsId, $cameraId);
        $tracker = $products->find($trackerId);
        if (!$tracker || (($tracker['item_category'] ?? '') !== 'Tracker')) throw new InvalidArgumentException('Select a tracker from inventory.');
        $trackerIdentifier = self::inventoryIdentifier($tracker, 'Tracker IMEI / serial');
        $data['device_type'] = (string) ($tracker['item_type'] ?? $tracker['device_type']);
        if ($simId > 0) { $sim = $products->find($simId); $data['sim_number'] = (string) ($sim['imei'] ?? ''); }
        $fls = null;
        $camera = null;
        foreach ([[$trackerId, 'Tracker'], [$flsId, 'FLS'], [$cameraId, 'Cameras'], [$simId, 'Sim cards']] as [$productId, $category]) {
            if ($productId < 1) { if ($category === 'Tracker') throw new InvalidArgumentException('Select a tracker from inventory.'); continue; }
            $product = $products->find($productId);
            if (!$product || (($product['item_category'] ?? '') !== $category)) throw new InvalidArgumentException('Select valid installation items from inventory.');
            if ($category === 'FLS') { self::inventoryIdentifier($product, 'FLS serial number'); $fls = $product; }
            if ($category === 'Cameras') { self::inventoryIdentifier($product, 'Camera serial number'); $camera = $product; }
            $products->consume($productId);
        }
        $data['imei'] = $trackerIdentifier;
        $data['serial'] = $trackerIdentifier;
        $data['fls_serial'] = $fls ? trim((string)$fls['imei']) : '';
        $data['camera_serial'] = $camera ? trim((string)$camera['imei']) : '';
        $sql = 'INSERT INTO gps_devices (device_name, device_type, package_type, imei, serial, sim_number, status, installed_at, created_at) VALUES (:device_name, :device_type, :package_type, :imei, :serial, :sim_number, :status, :installed_at, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':device_name' => trim((string) ($data['device_type'] ?? 'Galileosky 7x')),
            ':device_type' => trim((string) ($data['device_type'] ?? 'Galileosky 7x')),
            ':package_type' => trim((string) ($data['package_type'] ?? 'Basic installation')),
            ':imei' => trim((string) ($data['imei'] ?? '')),
            ':serial' => trim((string) ($data['serial'] ?? '')),
            ':sim_number' => trim((string) ($data['sim_number'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'available')),
            ':installed_at' => !empty($data['installed_at']) ? trim((string) $data['installed_at']) : null,
        ]);
        $id = $this->lastInsertId();
        $this->execute('UPDATE gps_devices SET tracker_product_id = :tracker, fls_product_id = :fls, camera_product_id = :camera, sim_product_id = :sim, fls_serial = :fls_serial, camera_serial = :camera_serial WHERE id = :id', [':tracker' => $trackerId, ':fls' => $flsId ?: null, ':camera' => $cameraId ?: null, ':sim' => $simId ?: null, ':fls_serial' => trim((string) ($data['fls_serial'] ?? '')), ':camera_serial' => trim((string) ($data['camera_serial'] ?? '')), ':id' => $id]);
        if ($ownsTransaction) $this->db->commit();
        return $id;
        } catch (Throwable $e) { if ($ownsTransaction && $this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function inventoryChoices(?array $device = null): array
    {
        $products = new Product();
        return [
            'trackers' => $products->availableForInstallation('Tracker', (int)($device['tracker_product_id'] ?? 0), true),
            'fls' => $products->availableForInstallation('FLS', (int)($device['fls_product_id'] ?? 0), true),
            'cameras' => $products->availableForInstallation('Cameras', (int)($device['camera_product_id'] ?? 0), true),
            'sims' => $products->availableForInstallation('Sim cards', (int)($device['sim_product_id'] ?? 0)),
        ];
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

    public function imeiExists(string $imei, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM gps_devices WHERE imei = :imei';
        $params = [':imei' => trim($imei)];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }
        return $this->fetchOne($sql, $params) !== null;
    }

    public function update(int $id, array $data): void
    {
        $this->db->beginTransaction();
        try {
            $current = $this->find($id);
            if (!$current) throw new InvalidArgumentException('Installation not found.');
            $trackerId = (int)($data['tracker_product_id'] ?? 0);
            $flsId = (int)($data['fls_product_id'] ?? 0);
            $cameraId = (int)($data['camera_product_id'] ?? 0);
            $simId = (int)($data['sim_product_id'] ?? 0);
            self::validatePackageSelection($data, $trackerId, $flsId, $cameraId);
            $products = new Product();
            foreach (['tracker_product_id', 'fls_product_id', 'camera_product_id', 'sim_product_id'] as $key) $products->restore((int)($current[$key] ?? 0));
            $tracker = $products->find($trackerId);
            if (!$tracker || ($tracker['item_category'] ?? '') !== 'Tracker') throw new InvalidArgumentException('Select a tracker from inventory.');
            $trackerIdentifier = self::inventoryIdentifier($tracker, 'Tracker IMEI / serial');
            $fls = null;
            $camera = null;
            foreach ([[$trackerId, 'Tracker'], [$flsId, 'FLS'], [$cameraId, 'Cameras'], [$simId, 'Sim cards']] as [$productId, $category]) {
                if ($productId < 1) continue;
                $product = $products->find($productId);
                if (!$product || ($product['item_category'] ?? '') !== $category) throw new InvalidArgumentException('Select valid installation items from inventory.');
                if ($category === 'FLS') { self::inventoryIdentifier($product, 'FLS serial number'); $fls = $product; }
                if ($category === 'Cameras') { self::inventoryIdentifier($product, 'Camera serial number'); $camera = $product; }
                $products->consume($productId);
            }
            $data['device_type'] = (string)($tracker['item_type'] ?? $tracker['device_type']);
            $data['imei'] = $trackerIdentifier;
            $data['serial'] = $trackerIdentifier;
            $sim = $simId > 0 ? $products->find($simId) : null;
            $this->execute('UPDATE gps_devices SET device_name = :device_name, device_type = :device_type, package_type = :package_type, imei = :imei, serial = :serial, sim_number = :sim_number, status = :status, installed_at = :installed_at, tracker_product_id = :tracker, fls_product_id = :fls, camera_product_id = :camera, sim_product_id = :sim, fls_serial = :fls_serial, camera_serial = :camera_serial WHERE id = :id', [
                ':device_name' => trim((string)$data['device_type']), ':device_type' => trim((string)$data['device_type']), ':package_type' => trim((string)($data['package_type'] ?? 'Basic')),
                ':imei' => $trackerIdentifier, ':serial' => $trackerIdentifier, ':sim_number' => (string)($sim['imei'] ?? ''),
                ':status' => trim((string)($current['status'] ?? 'available')), ':installed_at' => !empty($data['installed_at']) ? trim((string)$data['installed_at']) : null,
                ':tracker' => $trackerId, ':fls' => $flsId ?: null, ':camera' => $cameraId ?: null, ':sim' => $simId ?: null,
                ':fls_serial' => $fls ? trim((string)$fls['imei']) : '', ':camera_serial' => $camera ? trim((string)$camera['imei']) : '', ':id' => $id,
            ]);
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $this->db->beginTransaction();
        try {
            $device = $this->find($id);
            if ($device) {
                $products = new Product();
                foreach (['tracker_product_id', 'fls_product_id', 'camera_product_id', 'sim_product_id'] as $key) $products->restore((int)($device[$key] ?? 0));
                $this->execute('DELETE FROM gps_devices WHERE id = :id', [':id' => $id]);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    private static function validatePackageSelection(array $data, int $trackerId, int $flsId, int $cameraId): void
    {
        $package = (string)($data['package_type'] ?? '');
        $needsFls = in_array($package, ['Basic + FLS', 'Basic + FLS + Camera'], true);
        $needsCamera = $package === 'Basic + FLS + Camera';
        if (!in_array($package, self::packageTypes(), true) || $trackerId < 1 || ($needsFls && $flsId < 1) || (!$needsFls && $flsId > 0) || ($needsCamera && $cameraId < 1) || (!$needsCamera && $cameraId > 0)) {
            throw new InvalidArgumentException('Choose the equipment required by the selected installation package. Basic installations do not include FLS or a camera.');
        }
    }

    public function trackerImeiFromInventory(int $productId): ?string
    {
        $product = (new Product())->find($productId);
        if (!$product || ($product['item_category'] ?? '') !== 'Tracker') return null;
        try { return self::inventoryIdentifier($product, 'Tracker IMEI / serial'); }
        catch (InvalidArgumentException) { return null; }
    }

    private static function inventoryIdentifier(array $product, string $label): string
    {
        $identifier = trim((string)($product['imei'] ?? ''));
        if ($identifier === '' || !preg_match('/^\d+$/', $identifier)) {
            throw new InvalidArgumentException($label . ' must be entered in Inventory before this item can be installed.');
        }
        return $identifier;
    }

    public function countDevices(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM gps_devices')['total'] ?? 0);
    }
}
