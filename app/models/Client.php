<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Client extends Model
{
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM clients ORDER BY company_name ASC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM clients WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO clients (company_name, contact_name, email, phone, address, status, created_at) VALUES (:company_name, :contact_name, :email, :phone, :address, :status, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':company_name' => trim((string) ($data['company_name'] ?? '')),
            ':contact_name' => trim((string) ($data['contact_name'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':phone' => trim((string) ($data['phone'] ?? '')),
            ':address' => trim((string) ($data['address'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'active')),
        ]);
        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE clients SET company_name = :company_name, contact_name = :contact_name, email = :email, phone = :phone, address = :address, status = :status WHERE id = :id';
        $this->execute($sql, [
            ':company_name' => trim((string) ($data['company_name'] ?? '')),
            ':contact_name' => trim((string) ($data['contact_name'] ?? '')),
            ':email' => trim((string) ($data['email'] ?? '')),
            ':phone' => trim((string) ($data['phone'] ?? '')),
            ':address' => trim((string) ($data['address'] ?? '')),
            ':status' => trim((string) ($data['status'] ?? 'active')),
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM clients WHERE id = :id', [':id' => $id]);
    }
}
