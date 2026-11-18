<?php

require_once __DIR__ . '/settings.php';
require_once dirname(__DIR__) . '/services/ResendMailer.php';

if (!function_exists('sendMail')) {
    /**
     * Send an email through Resend. All outgoing mail (password resets and
     * low-stock alerts) uses this single entry point.
     */
    function sendMail(string $to, string $subject, string $message): bool
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $from = setting_string('mail_from', (string) $config['mail_from']);
        $apiKey = trim((string) ($config['resend_api_key'] ?? ''));

        return ResendMailer::send($to, $subject, $message, $from, $apiKey);
    }
}
