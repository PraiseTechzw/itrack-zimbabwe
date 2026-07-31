<?php

require_once dirname(__DIR__) . '/core/Model.php';

class User extends Model
{
    public function authenticate(string $email, string $password): ?array
    {
        $sql = 'SELECT * FROM users WHERE email = :email LIMIT 1';
        $user = $this->fetchOne($sql, [':email' => $email]);

        debugLog('User::authenticate called', [
            'email' => $email,
            'user_found' => $user !== null,
            'user_id' => $user['id'] ?? null,
            'password_hash_present' => isset($user['password_hash']),
        ]);

        if (!$user) {
            debugLog('User::authenticate no user', ['email' => $email]);
            return null;
        }

        if (!password_verify($password, $user['password_hash'])) {
            debugLog('User::authenticate bad password', [
                'email' => $email,
                'user_id' => $user['id'],
            ]);
            return null;
        }

        unset($user['password_hash']);
        debugLog('User::authenticate success', ['email' => $email, 'user_id' => $user['id']]);
        return $user;
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO users (name, email, password_hash, role, department, status, created_at) VALUES (:name, :email, :password_hash, :role, :department, :status, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':name' => $data['name'],
            ':email' => $data['email'],
            ':password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            ':role' => $data['role'],
            ':department' => $data['department'] ?? 'General',
            ':status' => $data['status'] ?? 'active',
        ]);
        return $this->lastInsertId();
    }

    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM users ORDER BY id DESC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = :id', [':id' => $id]);
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE email = :email LIMIT 1', [':email' => $email]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $this->execute('UPDATE users SET password_hash = :password_hash WHERE id = :id', [
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);
    }

    public function dashboardSummary(): array
    {
        $sql = 'SELECT COUNT(*) AS total_users FROM users';
        return $this->fetchOne($sql) ?? [];
    }
}
