<?php

require_once __DIR__ . '/settings.php';
require_once dirname(__DIR__) . '/services/ResendMailer.php';

if (!function_exists('mailSenderEmail')) {
    /**
     * Resolve the effective sender address. MAIL_FROM may be a full address or
     * just a verified domain (no-reply@ is added automatically).
     */
    function mailSenderEmail(): string
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $domain = trim((string) ($config['mail_domain'] ?? ''), '.');
        $from = setting_string('mail_from', (string) $config['mail_from']);

        // A bare domain becomes a no-reply address on that domain.
        if ($from !== '' && !str_contains($from, '@')) {
            $from = 'no-reply@' . trim($from, '.');
        } elseif ($from === '') {
            $from = $domain !== '' ? 'no-reply@' . $domain : '';
        }

        $address = ResendMailer::normalizeFrom($from);
        if ($address === false) {
            $address = ResendMailer::normalizeFrom($domain !== '' ? 'no-reply@' . $domain : 'onboarding@resend.dev');
        }
        return $address === false ? 'onboarding@resend.dev' : $address;
    }
}

if (!function_exists('mailSender')) {
    /** Sender address with the configured display name, ready for Resend. */
    function mailSender(): string
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $address = mailSenderEmail();
        $name = trim((string) ($config['mail_from_name'] ?? ''));
        if ($name === '') return $address;

        $named = ResendMailer::normalizeFrom($name . ' <' . $address . '>');
        return $named === false ? $address : $named;
    }
}

if (!function_exists('sendMail')) {
    /**
     * Send an email through Resend. All outgoing mail (password resets and
     * low-stock alerts) uses this single entry point.
     */
    function sendMail(string $to, string $subject, string $text, string $html = ''): bool
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        return ResendMailer::send(
            $to,
            $subject,
            $text,
            mailSender(),
            trim((string) ($config['resend_api_key'] ?? '')),
            $html
        );
    }
}
