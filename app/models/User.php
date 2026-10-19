<?php

require_once dirname(__DIR__) . '/core/Model.php';

class User extends Model
{
    public function authenticate(string $email, string $password): ?array
    {
        $sql = "SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1";
        $user = $this->fetchOne($sql, [':email' => $email]);
        if (!$user || !password_verify($password, $user['password_hash'])) return null;

        unset($user['password_hash']);
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

    public function update(int $id, array $data): void
    {
        $fields = ['name', 'email', 'role', 'department', 'status'];
        $values = [];
        foreach ($fields as $field) {
            $values[$field] = trim((string) ($data[$field] ?? ''));
        }
        $sql = 'UPDATE users SET name=:name, email=:email, role=:role, department=:department, status=:status';
        $params = [':name'=>$values['name'], ':email'=>$values['email'], ':role'=>$values['role'], ':department'=>$values['department'] ?: 'General', ':status'=>$values['status'] ?: 'active', ':id'=>$id];
        if (trim((string) ($data['password'] ?? '')) !== '') {
            $sql .= ', password_hash=:password_hash';
            $params[':password_hash'] = password_hash((string) $data['password'], PASSWORD_DEFAULT);
        }
        $this->execute($sql . ' WHERE id=:id', $params);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM users WHERE id=:id', [':id'=>$id]);
    }

    public function updatePassword(int $id, string $password): void
    {
        $this->execute('UPDATE users SET password_hash = :password_hash WHERE id = :id', [
            ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
            ':id' => $id,
        ]);
    }

    public function createPasswordResetToken(string $email): ?string
    {
        $user = $this->fetchOne("SELECT id FROM users WHERE email = :email AND status = 'active' LIMIT 1", [':email' => $email]);
        if (!$user) return null;

        $token = bin2hex(random_bytes(32));
        $this->execute('DELETE FROM password_reset_tokens WHERE user_id = ?', [(int) $user['id']]);
        $this->execute('INSERT INTO password_reset_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)', [(int) $user['id'], hash('sha256', $token), gmdate('Y-m-d H:i:s', time() + 3600)]);
        return $token;
    }

    public function removePasswordResetTokensForEmail(string $email): void
    {
        $this->execute('DELETE FROM password_reset_tokens WHERE user_id IN (SELECT id FROM users WHERE email = ?)', [$email]);
    }

    public function resetPassword(string $token, string $password): bool
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) return false;
        $record = $this->fetchOne('SELECT id, user_id FROM password_reset_tokens WHERE token_hash = ? AND expires_at > ? LIMIT 1', [hash('sha256', $token), gmdate('Y-m-d H:i:s')]);
        if (!$record) return false;

        $this->db->beginTransaction();
        try {
            $this->execute('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), (int) $record['user_id']]);
            $this->execute('DELETE FROM password_reset_tokens WHERE user_id = ?', [(int) $record['user_id']]);
            $this->db->commit();
            return true;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public function verifyPassword(int $id, string $password): bool
    {
        $user = $this->fetchOne('SELECT password_hash FROM users WHERE id = ?', [$id]);
        return $user !== null && password_verify($password, $user['password_hash']);
    }

    public function dashboardSummary(): array
    {
        $sql = 'SELECT COUNT(*) AS total_users FROM users';
        return $this->fetchOne($sql) ?? [];
    }
}
