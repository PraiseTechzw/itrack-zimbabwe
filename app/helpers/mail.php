<?php

if (!function_exists('sendMail')) {
    function sendMail(string $to, string $subject, string $message): bool
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $from = trim((string) $config['mail_from']);
        if (!filter_var($to, FILTER_VALIDATE_EMAIL) || !filter_var($from, FILTER_VALIDATE_EMAIL)) return false;
        if (preg_match('/[\r\n]/', $subject) || preg_match('/[\r\n]/', $from)) return false;

        $headers = "From: {$from}\r\n" . "Content-Type: text/plain; charset=UTF-8\r\n";
        return @mail($to, $subject, $message, $headers);
    }
}
