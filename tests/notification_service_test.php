<?php
require_once __DIR__ . '/../app/helpers/notifications.php';

if (!function_exists('createNotification')) {
    fwrite(STDERR, "Missing createNotification helper\n");
    exit(1);
}

$notification = createNotification(1, 'Test alert', 'This is a system-generated notification.');
if (!is_int($notification) || $notification <= 0) {
    fwrite(STDERR, "Notification was not created\n");
    exit(1);
}

echo "notification service ok\n";
