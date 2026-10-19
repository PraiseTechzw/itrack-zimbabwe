<?php

require_once dirname(__DIR__) . '/core/Model.php';

class GPSDevice extends Model
{
    public static function deviceTypes(): array
    {
        return ['Galileosky 7x', 'Teltonika FMB920', 'Smart S-24xx', 'Teltonika FMC920', 'Teltonika FMB140', 'Teltonika FMC130', 'Queclink GV300W', 'Concox GT06N', 'Ruptela Trace5', 'Howen Hero-ME40'];
    }
    public static function packageTypes(): array
    {
        return ['Basic installation', 'Basic FLS + camera installation', 'Basic + FLS', 'Basic installation + tracking', 'Basic installation + camera', 'FLS installation only', 'Camera installation only', 'Tracking + FLS + camera', 'Device supply only', 'Maintenance / inspection'];
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
        return $this->lastInsertId();
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
