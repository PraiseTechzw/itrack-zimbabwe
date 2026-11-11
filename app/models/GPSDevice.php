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
        return ['Basic', 'Basic + FLS', 'Basic + FLS + Camera', 'Camera only', 'Camera + Basic'];
    }
    public function all(): array
    {
        return $this->fetchAll('SELECT g.*, c.company_name AS client_name, i.name AS installer_name, s.name AS support_name FROM gps_devices g LEFT JOIN clients c ON c.id = g.client_id LEFT JOIN users i ON i.id = g.installer_id LEFT JOIN users s ON s.id = g.support_id ORDER BY g.device_name ASC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT g.*, c.company_name AS client_name, i.name AS installer_name, s.name AS support_name FROM gps_devices g LEFT JOIN clients c ON c.id = g.client_id LEFT JOIN users i ON i.id = g.installer_id LEFT JOIN users s ON s.id = g.support_id WHERE g.id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $trackerId = (int) ($data['tracker_product_id'] ?? 0);
        $flsId = (int) ($data['fls_product_id'] ?? 0);
        $cameraId = (int) ($data['camera_product_id'] ?? 0);
        $simId = (int) ($data['sim_product_id'] ?? 0);
        $cameraSimId = (int) ($data['camera_sim_product_id'] ?? 0);
        if ($simId > 0 && $simId === $cameraSimId) throw new InvalidArgumentException('Choose different SIM lines for the tracker and camera.');
        $products = new Product();
        $ownsTransaction = !$this->db->inTransaction();
        if ($ownsTransaction) $this->db->beginTransaction();
        try {
            self::validatePackageSelection($data, $trackerId, $flsId, $cameraId);
            $cameraOnly = (string)($data['package_type'] ?? '') === 'Camera only';
            $tracker = $trackerId > 0 ? $products->find($trackerId) : null;
            if (!$cameraOnly && (!$tracker || ($tracker['item_category'] ?? '') !== 'Tracker')) throw new InvalidArgumentException('Select a tracker from inventory.');
            $trackerIdentifier = $tracker ? self::resolveTrackerIdentifier($tracker, (string)($data['tracker_identifier'] ?? '')) : null;
            $fls = null;
            $camera = null;
            $deviceSim = null;
            $cameraSim = null;
            $selectedProducts = [[$flsId, 'FLS'], [$cameraId, 'Cameras'], [$simId, 'Sim cards'], [$cameraSimId, 'Sim cards']];
            if ($trackerId > 0) array_unshift($selectedProducts, [$trackerId, 'Tracker']);
            foreach ($selectedProducts as [$productId, $category]) {
                if ($productId < 1) {
                    continue;
                }
                $product = $products->find($productId);
                if (!$product || ($product['item_category'] ?? '') !== $category) throw new InvalidArgumentException('Select valid installation items from inventory.');
                if ($category === 'FLS') { self::inventoryIdentifier($product, 'FLS serial number'); $fls = $product; }
                if ($category === 'Cameras') { self::inventoryIdentifier($product, 'Camera serial number'); $camera = $product; }
                if ($category === 'Sim cards') {
                    self::inventoryIdentifier($product, 'SIM line number');
                    if ($productId === $simId) $deviceSim = $product;
                    if ($productId === $cameraSimId) $cameraSim = $product;
                }
                $products->consume($productId);
            }
            $deviceType = (string)($tracker['item_type'] ?? 'Camera');
            $recordIdentifier = $trackerIdentifier ?? $this->cameraOnlyReference();
            $this->execute(
                'INSERT INTO gps_devices (device_name, device_type, package_type, imei, serial, sim_number, camera_sim_number, status, installed_at, client_id, installer_id, support_id, registration_number, chassis_number, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)',
                [
                    trim($deviceType),
                    trim($deviceType),
                    trim((string)($data['package_type'] ?? 'Basic')),
                    $recordIdentifier,
                    $trackerIdentifier,
                    trim((string)($deviceSim['imei'] ?? '')),
                    trim((string)($cameraSim['imei'] ?? '')),
                    'installed',
                    !empty($data['installed_at']) ? trim((string)$data['installed_at']) : null,
                    (int)($data['client_id'] ?? 0) ?: null,
                    (int)($data['installer_id'] ?? 0) ?: null,
                    (int)($data['support_id'] ?? 0) ?: null,
                    trim((string)($data['registration_number'] ?? '')),
                    trim((string)($data['chassis_number'] ?? '')),
                ]
            );
            $id = $this->lastInsertId();
            $this->execute(
                'UPDATE gps_devices SET tracker_product_id = ?, fls_product_id = ?, camera_product_id = ?, sim_product_id = ?, camera_sim_product_id = ?, fls_serial = ?, camera_serial = ? WHERE id = ?',
                [$trackerId ?: null, $flsId ?: null, $cameraId ?: null, $simId ?: null, $cameraSimId ?: null, trim((string)($fls['imei'] ?? '')), trim((string)($camera['imei'] ?? '')), $id]
            );
            if ($ownsTransaction) $this->db->commit();
            return $id;
        } catch (Throwable $e) { if ($ownsTransaction && $this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function inventoryChoices(?array $device = null): array
    {
        $products = new Product();
        return [
            'trackers' => $products->availableForInstallation('Tracker', (int)($device['tracker_product_id'] ?? 0)),
            'fls' => $products->availableForInstallation('FLS', (int)($device['fls_product_id'] ?? 0), true),
            'cameras' => $products->availableForInstallation('Cameras', (int)($device['camera_product_id'] ?? 0), true),
            'sims' => $this->simChoices($products, $device),
        ];
    }

    private function simChoices(Product $products, ?array $device): array
    {
        $selectedIds = array_filter([
            (int)($device['sim_product_id'] ?? 0),
            (int)($device['camera_sim_product_id'] ?? 0),
        ]);
        $choices = $products->availableForInstallation('Sim cards', (int)($selectedIds[0] ?? 0), true);
        $knownIds = array_map(static fn(array $item): int => (int)$item['id'], $choices);
        foreach (array_slice($selectedIds, 1) as $id) {
            if (!in_array($id, $knownIds, true)) {
                $item = $products->find($id);
                if ($item && ($item['item_category'] ?? '') === 'Sim cards') $choices[] = $item;
            }
        }
        return $choices;
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
            $cameraSimId = (int)($data['camera_sim_product_id'] ?? 0);
            if (($current['status'] ?? '') === 'deinstalled') throw new InvalidArgumentException('A deinstalled record cannot be edited as an active installation.');
            if ($simId > 0 && $simId === $cameraSimId) throw new InvalidArgumentException('Choose different SIM lines for the tracker and camera.');
            self::validatePackageSelection($data, $trackerId, $flsId, $cameraId);
            $products = new Product();
            foreach (['tracker_product_id', 'fls_product_id', 'camera_product_id', 'sim_product_id', 'camera_sim_product_id'] as $key) $products->restore((int)($current[$key] ?? 0));
            $cameraOnly = (string)($data['package_type'] ?? '') === 'Camera only';
            $tracker = $trackerId > 0 ? $products->find($trackerId) : null;
            if (!$cameraOnly && (!$tracker || ($tracker['item_category'] ?? '') !== 'Tracker')) throw new InvalidArgumentException('Select a tracker from inventory.');
            $trackerIdentifier = $tracker ? self::resolveTrackerIdentifier($tracker, (string)($data['tracker_identifier'] ?? '')) : null;
            $fls = null;
            $camera = null;
            $deviceSim = null;
            $cameraSim = null;
            $selectedProducts = [[$flsId, 'FLS'], [$cameraId, 'Cameras'], [$simId, 'Sim cards'], [$cameraSimId, 'Sim cards']];
            if ($trackerId > 0) array_unshift($selectedProducts, [$trackerId, 'Tracker']);
            foreach ($selectedProducts as [$productId, $category]) {
                if ($productId < 1) continue;
                $product = $products->find($productId);
                if (!$product || ($product['item_category'] ?? '') !== $category) throw new InvalidArgumentException('Select valid installation items from inventory.');
                if ($category === 'FLS') { self::inventoryIdentifier($product, 'FLS serial number'); $fls = $product; }
                if ($category === 'Cameras') { self::inventoryIdentifier($product, 'Camera serial number'); $camera = $product; }
                if ($category === 'Sim cards') {
                    self::inventoryIdentifier($product, 'SIM line number');
                    if ($productId === $simId) $deviceSim = $product;
                    if ($productId === $cameraSimId) $cameraSim = $product;
                }
                $products->consume($productId);
            }
            $deviceType = (string)($tracker['item_type'] ?? 'Camera');
            $recordIdentifier = $trackerIdentifier ?? $this->cameraOnlyReference();
            $this->execute(
                'UPDATE gps_devices SET device_name = ?, device_type = ?, package_type = ?, imei = ?, serial = ?, sim_number = ?, camera_sim_number = ?, status = ?, installed_at = ?, client_id = ?, installer_id = ?, support_id = ?, registration_number = ?, chassis_number = ?, tracker_product_id = ?, fls_product_id = ?, camera_product_id = ?, sim_product_id = ?, camera_sim_product_id = ?, fls_serial = ?, camera_serial = ? WHERE id = ?',
                [
                    trim($deviceType),
                    trim($deviceType),
                    trim((string)($data['package_type'] ?? 'Basic')),
                    $recordIdentifier,
                    $trackerIdentifier,
                    trim((string)($deviceSim['imei'] ?? '')),
                    trim((string)($cameraSim['imei'] ?? '')),
                    'installed',
                    !empty($data['installed_at']) ? trim((string)$data['installed_at']) : null,
                    (int)($data['client_id'] ?? 0) ?: null,
                    (int)($data['installer_id'] ?? 0) ?: null,
                    (int)($data['support_id'] ?? 0) ?: null,
                    trim((string)($data['registration_number'] ?? '')),
                    trim((string)($data['chassis_number'] ?? '')),
                    $trackerId ?: null,
                    $flsId ?: null,
                    $cameraId ?: null,
                    $simId ?: null,
                    $cameraSimId ?: null,
                    trim((string)($fls['imei'] ?? '')),
                    trim((string)($camera['imei'] ?? '')),
                    $id,
                ]
            );
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
                if (($device['status'] ?? '') !== 'deinstalled') {
                    foreach (['tracker_product_id', 'fls_product_id', 'camera_product_id', 'sim_product_id', 'camera_sim_product_id'] as $key) $products->restore((int)($device[$key] ?? 0));
                }
                $this->execute('DELETE FROM gps_devices WHERE id = :id', [':id' => $id]);
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function activeForDeinstallation(): array
    {
        return $this->fetchAll("SELECT * FROM gps_devices WHERE status = 'installed' ORDER BY device_name, id");
    }

    public function deinstallations(): array
    {
        return $this->fetchAll('SELECT d.*, c.company_name AS client_name, u.name AS removed_by FROM deinstallations d LEFT JOIN clients c ON c.id = d.client_id LEFT JOIN users u ON u.id = d.created_by ORDER BY d.removed_at DESC, d.id DESC');
    }

    public function deinstall(int $id, array $data, int $userId): void
    {
        $this->db->beginTransaction();
        try {
            $device = $this->find($id);
            if (!$device || ($device['status'] ?? '') !== 'installed') throw new InvalidArgumentException('Select an active installation to deinstall.');
            $registration = trim((string)($data['registration_number'] ?? ''));
            $chassis = trim((string)($data['chassis_number'] ?? ''));
            $removedAt = trim((string)($data['removed_at'] ?? ''));
            $removalDate = DateTimeImmutable::createFromFormat('!Y-m-d', $removedAt);
            $dateErrors = DateTimeImmutable::getLastErrors();
            if ($registration === '' || $chassis === '' || $removalDate === false
                || ($dateErrors !== false && ($dateErrors['warning_count'] > 0 || $dateErrors['error_count'] > 0))
                || $removalDate->format('Y-m-d') !== $removedAt) {
                throw new InvalidArgumentException('Enter the vehicle registration, chassis number, and a valid removal date.');
            }
            $products = new Product();
            foreach (['tracker_product_id', 'fls_product_id', 'camera_product_id', 'sim_product_id', 'camera_sim_product_id'] as $key) {
                $products->restore((int)($device[$key] ?? 0));
            }
            $reason = trim((string)($data['reason'] ?? ''));
            $this->execute(
                'INSERT INTO deinstallations (gps_device_id, client_id, device_type, imei, camera_serial, registration_number, chassis_number, removed_at, reason, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $id,
                    (int)($device['client_id'] ?? 0) ?: null,
                    (string)($device['device_type'] ?? ''),
                    ($device['package_type'] ?? '') === 'Camera only' ? null : (string)($device['imei'] ?? ''),
                    (string)($device['camera_serial'] ?? ''),
                    $registration,
                    $chassis,
                    $removedAt,
                    $reason,
                    $userId ?: null,
                ]
            );
            $this->execute('UPDATE gps_devices SET status = ?, deinstalled_at = ?, registration_number = ?, chassis_number = ? WHERE id = ?', ['deinstalled', $removedAt, $registration, $chassis, $id]);
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    private static function validatePackageSelection(array $data, int $trackerId, int $flsId, int $cameraId): void
    {
        $package = (string)($data['package_type'] ?? '');
        $cameraOnly = $package === 'Camera only';
        $needsFls = in_array($package, ['Basic + FLS', 'Basic + FLS + Camera'], true);
        $needsCamera = in_array($package, ['Basic + FLS + Camera', 'Camera only', 'Camera + Basic'], true);
        if (!in_array($package, self::packageTypes(), true) || (!$cameraOnly && $trackerId < 1) || ($cameraOnly && $trackerId > 0) || ($needsFls && $flsId < 1) || (!$needsFls && $flsId > 0) || ($needsCamera && $cameraId < 1) || (!$needsCamera && $cameraId > 0)) {
            throw new InvalidArgumentException('Choose the equipment required by the selected installation package. Camera only does not require a tracker; other packages require their listed equipment.');
        }
    }

    private function cameraOnlyReference(): ?string
    {
        if ($this->db->getAttribute(PDO::ATTR_DRIVER_NAME) !== 'sqlite') return null;
        $columns = $this->db->query('PRAGMA table_info(gps_devices)')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if (($column['name'] ?? '') === 'imei' && (int)($column['notnull'] ?? 0) === 1) {
                return 'CAMERA-' . strtoupper(bin2hex(random_bytes(8)));
            }
        }
        return null;
    }

    public function trackerIdentifierFromInventory(int $productId, string $identifier = ''): ?string
    {
        $product = (new Product())->find($productId);
        if (!$product || ($product['item_category'] ?? '') !== 'Tracker') return null;
        try { return self::resolveTrackerIdentifier($product, $identifier); }
        catch (InvalidArgumentException) { return null; }
    }

    private static function resolveTrackerIdentifier(array $product, string $providedIdentifier): string
    {
        $inventoryIdentifier = trim((string)($product['imei'] ?? ''));
        $identifier = $inventoryIdentifier !== '' ? $inventoryIdentifier : trim($providedIdentifier);
        if ($identifier === '' || !preg_match('/^\d+$/', $identifier)) {
            throw new InvalidArgumentException('Enter a numeric tracker IMEI / serial for this installation.');
        }
        return $identifier;
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
