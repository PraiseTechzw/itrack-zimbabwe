<?php

/**
 * Sends transactional email through the Resend HTTP API.
 *
 * The API key is read from the environment only (RESEND_API_KEY), matching how
 * other secrets are handled in this application. Resend responses are logged on
 * failure and exposed through lastError() so callers can surface a useful hint.
 */
class ResendMailer
{
    private const ENDPOINT = 'https://api.resend.com/emails';

    private static string $lastError = '';

    public static function lastError(): string
    {
        return self::$lastError;
    }

    /** Normalize a sender as "email@domain" or "Display Name <email@domain>". */
    public static function normalizeFrom(string $from): string|false
    {
        $from = trim($from);
        if ($from === '' || preg_match('/[\r\n]/', $from)) return false;

        $name = '';
        $email = $from;
        if (preg_match('/^(.*?)\s*<([^<>]+)>$/', $from, $matches) === 1) {
            $name = trim($matches[1], " \t\"'");
            $email = trim($matches[2]);
            if ($name === '' || preg_match('/[\r\n<>]/', $name)) return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

        return $name !== '' ? $name . ' <' . $email . '>' : $email;
    }

    /** Build and validate the Resend payload without performing any I/O. */
    public static function payload(string $to, string $from, string $subject, string $message): array|false
    {
        $from = self::normalizeFrom($from);
        if ($from === false || !filter_var($to, FILTER_VALIDATE_EMAIL)) return false;
        if (preg_match('/[\r\n]/', $to) || preg_match('/[\r\n]/', $subject)) return false;

        $subject = trim($subject);
        if ($subject === '') return false;

        return [
            'from' => $from,
            'to' => [$to],
            'subject' => $subject,
            'text' => $message,
        ];
    }

    /** Send one message. Returns true only when Resend accepts it (HTTP 2xx). */
    public static function send(string $to, string $subject, string $message, string $from, string $apiKey): bool
    {
        self::$lastError = '';

        if ($apiKey === '') {
            self::$lastError = 'RESEND_API_KEY is not configured.';
            error_log('Resend mail: RESEND_API_KEY is not configured.');
            return false;
        }

        $payload = self::payload($to, $from, $subject, $message);
        if ($payload === false) {
            self::$lastError = 'Invalid recipient, sender, or subject.';
            return false;
        }

        if (!function_exists('curl_init')) {
            self::$lastError = 'The PHP cURL extension is required for Resend.';
            error_log('Resend mail: cURL extension is not available.');
            return false;
        }

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            self::$lastError = 'Unable to encode the email payload.';
            return false;
        }

        $handle = curl_init(self::ENDPOINT);
        curl_setopt_array($handle, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'Accept: application/json',
                'User-Agent: itrack-zimbabwe',
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($handle);
        $status = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $curlError = curl_error($handle);
        curl_close($handle);

        if ($response === false || $status < 200 || $status >= 300) {
            $decoded = is_string($response) ? json_decode($response, true) : null;
            $detail = is_array($decoded) ? (string) ($decoded['message'] ?? $decoded['name'] ?? '') : '';
            if ($detail === '') $detail = $curlError !== '' ? $curlError : 'HTTP ' . $status;
            self::$lastError = $detail;
            error_log('Resend mail error: ' . $detail);
            return false;
        }

        return true;
    }
}
