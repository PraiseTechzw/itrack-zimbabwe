<?php

require_once dirname(__DIR__) . '/helpers/mail.php';
require_once dirname(__DIR__) . '/helpers/settings.php';
require_once dirname(__DIR__) . '/services/EmailTemplate.php';

/** Builds and throttles low-stock summary emails. */
class LowStockAlerter
{
    public static function recipients(string $raw): array
    {
        $emails = array_filter(array_map('trim', explode(',', $raw)), static fn(string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false);
        return array_values(array_unique($emails));
    }

    public static function shouldSend(array $settings): bool
    {
        if (!in_array(strtolower((string)($settings['low_stock_email_enabled'] ?? '0')), ['1', 'true', 'yes', 'on'], true)) return false;
        if (self::recipients((string)($settings['low_stock_email_recipients'] ?? '')) === []) return false;
        $last = trim((string)($settings['low_stock_last_sent_at'] ?? ''));
        if ($last === '') return true;
        $timestamp = strtotime($last);
        if ($timestamp === false) return true;
        $intervalHours = max(1, (int)($settings['low_stock_email_interval_hours'] ?? 24));
        return $timestamp <= time() - ($intervalHours * 3600);
    }

    /** Called on page loads; never sends more often than the configured interval. */
    public static function maybeSend(array $settings, array $items): array
    {
        if ($items === [] || !self::shouldSend($settings)) return ['status' => 'skipped'];
        return self::send($settings, $items);
    }

    public static function send(array $settings, array $items): array
    {
        $recipients = self::recipients((string)($settings['low_stock_email_recipients'] ?? ''));
        if ($recipients === []) return ['status' => 'error', 'message' => 'Add at least one valid recipient email address.'];
        if ($items === []) return ['status' => 'skipped', 'message' => 'No items are currently at or below their reorder level.'];

        $config = require dirname(__DIR__) . '/config/app.php';
        $appName = setting_string('app_name', (string) ($config['app_name'] ?? 'iTrack Zimbabwe'));
        $publicUrl = rtrim((string) ($config['public_url'] ?? ''), '/');
        $inventoryUrl = $publicUrl !== '' ? $publicUrl . '/index.php?controller=inventory' : '';
        $template = EmailTemplate::lowStock($appName, $items, $inventoryUrl);

        $sent = 0;
        foreach ($recipients as $email) {
            if (sendMail($email, $template['subject'], $template['text'], $template['html'])) $sent++;
        }
        if ($sent === 0) {
            $hint = trim(ResendMailer::lastError());
            return ['status' => 'error', 'message' => 'Resend could not deliver the alert.' . ($hint !== '' ? ' ' . $hint : '')];
        }

        (new AppSetting())->save(['low_stock_last_sent_at' => date('c')]);
        return ['status' => 'sent', 'message' => "Low-stock summary sent to {$sent} recipient(s)."];
    }
}
