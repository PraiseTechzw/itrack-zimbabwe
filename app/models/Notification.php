<?php

require_once dirname(__DIR__) . '/core/Model.php';

class Notification extends Model
{
    public function all(?int $userId = null): array
    {
        $sql = 'SELECT * FROM notifications';
        $params = [];

        if ($userId !== null) {
            $sql .= ' WHERE user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        $sql .= ' ORDER BY created_at DESC';
        return $this->fetchAll($sql, $params);
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM notifications WHERE id = :id', [':id' => $id]);
    }

    public function create(array $data): string
    {
        $sql = 'INSERT INTO notifications (user_id, title, message, is_read, created_at) VALUES (:user_id, :title, :message, :is_read, CURRENT_TIMESTAMP)';
        $this->execute($sql, [
            ':user_id' => $data['user_id'] !== '' ? (int) $data['user_id'] : null,
            ':title' => trim((string) ($data['title'] ?? '')),
            ':message' => trim((string) ($data['message'] ?? '')),
            ':is_read' => isset($data['is_read']) && $data['is_read'] ? 1 : 0,
        ]);

        return $this->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE notifications SET user_id = :user_id, title = :title, message = :message, is_read = :is_read WHERE id = :id';
        $this->execute($sql, [
            ':user_id' => $data['user_id'] !== '' ? (int) $data['user_id'] : null,
            ':title' => trim((string) ($data['title'] ?? '')),
            ':message' => trim((string) ($data['message'] ?? '')),
            ':is_read' => isset($data['is_read']) && $data['is_read'] ? 1 : 0,
            ':id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->execute('DELETE FROM notifications WHERE id = :id', [':id' => $id]);
    }

    public function unreadCount(?int $userId = null): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM notifications WHERE is_read = 0';
        $params = [];

        if ($userId !== null) {
            $sql .= ' AND user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        return (int) ($this->fetchOne($sql, $params)['total'] ?? 0);
    }

    public function countAll(?int $userId = null): int
    {
        $sql = 'SELECT COUNT(*) AS total FROM notifications';
        $params = [];

        if ($userId !== null) {
            $sql .= ' WHERE user_id = :user_id';
            $params[':user_id'] = $userId;
        }

        return (int) ($this->fetchOne($sql, $params)['total'] ?? 0);
    }
}
