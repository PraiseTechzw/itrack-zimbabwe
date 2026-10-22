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
        $this->db->beginTransaction();
        try {
        $tracker = $products->find($trackerId);
        if (!$tracker || (($tracker['item_category'] ?? '') !== 'Tracker')) throw new InvalidArgumentException('Select a tracker from inventory.');
        $data['device_type'] = (string) ($tracker['item_type'] ?? $tracker['device_type']);
        if ($simId > 0) { $sim = $products->find($simId); $data['sim_number'] = (string) ($sim['imei'] ?? ''); }
        foreach ([[$trackerId, 'Tracker'], [$flsId, 'FLS'], [$cameraId, 'Cameras'], [$simId, 'Sim cards']] as [$productId, $category]) {
            if ($productId < 1) { if ($category === 'Tracker') throw new InvalidArgumentException('Select a tracker from inventory.'); continue; }
            $product = $products->find($productId);
            if (!$product || (($product['item_category'] ?? '') !== $category)) throw new InvalidArgumentException('Select valid installation items from inventory.');
            $products->consume($productId);
        }
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
        $this->db->commit();
        return $id;
        } catch (Throwable $e) { if ($this->db->inTransaction()) $this->db->rollBack(); throw $e; }
    }

    public function inventoryChoices(): array
    {
        $products = new Product();
        return ['trackers' => $products->availableForInstallation('Tracker'), 'fls' => $products->availableForInstallation('FLS'), 'cameras' => $products->availableForInstallation('Cameras'), 'sims' => $products->availableForInstallation('Sim cards')];
    }

    public function createMany(array $devices, array $data): void
    {
        $this->db->beginTransaction();
        try {
            foreach ($devices as $device) {
                $data['imei'] = $device['imei'];
                $data['sim_number'] = $device['sim_number'];
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
        $sql = 'UPDATE gps_devices SET device_name = :device_name, device_type = :device_type, package_type = :package_type, imei = :imei, serial = :serial, sim_number = :sim_number, status = :status, installed_at = :installed_at WHERE id = :id';
        $this->execute($sql, [
            ':device_name' => trim((string) ($data['device_type'] ?? 'Galileosky 7x')),
            ':device_type' => trim((string) ($data['device_type'] ?? 'Galileosky 7x')),
            ':package_type' => trim((string) ($data['package_type'] ?? 'Basic installation')),
            ':imei' => trim((string) ($data['imei'] ?? '')),
            ':serial' => trim((string) ($data['serial'] ?? '')),
            ':sim_number' => trim((string) ($data['sim_number'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'available')),
            ':installed_at' => !empty($data['installed_at']) ? trim((string) $data['installed_at']) : null,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM gps_devices WHERE id = :id', [':id' => $id]);
    }

    public function countDevices(): int
    {
        return (int) ($this->fetchOne('SELECT COUNT(*) AS total FROM gps_devices')['total'] ?? 0);
    }
}
