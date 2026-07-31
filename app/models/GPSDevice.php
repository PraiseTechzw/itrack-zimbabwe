<?php

require_once dirname(__DIR__) . '/core/Model.php';

class GPSDevice extends Model
{
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
        $sql = 'INSERT INTO gps_devices (device_name, imei, serial, sim_number, status, installed_at, created_at) VALUES (:device_name, :imei, :serial, :sim_number, :status, :installed_at, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':device_name' => trim((string) ($data['device_name'] ?? '')),
            ':imei' => trim((string) ($data['imei'] ?? '')),
            ':serial' => trim((string) ($data['serial'] ?? '')),
            ':sim_number' => trim((string) ($data['sim_number'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'available')),
            ':installed_at' => !empty($data['installed_at']) ? trim((string) $data['installed_at']) : null,
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE gps_devices SET device_name = :device_name, imei = :imei, serial = :serial, sim_number = :sim_number, status = :status, installed_at = :installed_at WHERE id = :id';
        $this->execute($sql, [
            ':device_name' => trim((string) ($data['device_name'] ?? '')),
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
}
