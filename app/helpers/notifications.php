<?php

require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/User.php';

function createNotification(?int $userId, string $title, string $message): int
{
    $cleanTitle = trim((string) $title);
    $cleanMessage = trim((string) $message);

    if ($cleanTitle === '' || $cleanMessage === '') {
        return 0;
    }

    $notificationModel = new Notification();
    $result = $notificationModel->create([
        'user_id' => $userId,
        'title' => $cleanTitle,
        'message' => $cleanMessage,
        'is_read' => 0,
    ]);

    return (int) $result;
}

function notifyUsers(array $userIds, string $title, string $message): array
{
    $createdIds = [];
    foreach ($userIds as $userId) {
        $parsedId = (int) $userId;
        if ($parsedId > 0) {
            $createdId = createNotification($parsedId, $title, $message);
            if ($createdId > 0) {
                $createdIds[] = $createdId;
            }
        }
    }

    return $createdIds;
}

function notifyRoles(array $roles, string $title, string $message): array
{
    $roles = array_map('strval', $roles);
    $userModel = new User();
    $users = $userModel->all();
    $userIds = [];

    foreach ($users as $user) {
        $role = trim((string) ($user['role'] ?? ''));
        if ($role !== '' && in_array($role, $roles, true)) {
            $userIds[] = (int) ($user['id'] ?? 0);
        }
    }

    return notifyUsers($userIds, $title, $message);
}

function notifyAdmins(string $title, string $message): array
{
    return notifyRoles(['Administrator'], $title, $message);
}

function notifyTeam(array $roles, string $title, string $message): array
{
    return notifyRoles($roles, $title, $message);
}
