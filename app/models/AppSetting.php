<?php

require_once dirname(__DIR__) . '/core/Model.php';

/**
 * Persistent application settings. Environment variables remain the bootstrap
 * fallback; values saved here override them for the running installation.
 */
class AppSetting extends Model
{
    /** Settings catalogue. Also drives the Settings screen and validation. */
    public static function definitions(): array
    {
        return [
            'general' => [
                'app_name' => ['label' => 'Application name', 'type' => 'text', 'default' => 'iTrack Zimbabwe', 'max' => 100, 'help' => 'Shown in the browser title and sign-in screen.'],
                'app_tagline' => ['label' => 'Tagline', 'type' => 'text', 'default' => 'Operations platform', 'max' => 120, 'help' => 'Short description under the application name.'],
                'public_url' => ['label' => 'Public URL', 'type' => 'text', 'default' => '', 'max' => 255, 'help' => 'Absolute base URL used in password-reset emails. Leave blank to use the request host.'],
                'allow_registration' => ['label' => 'Allow staff registration', 'type' => 'bool', 'default' => false, 'help' => 'Lets visitors create their own account. Keep off in production.'],
                'debug' => ['label' => 'Debug logging', 'type' => 'bool', 'default' => false, 'help' => 'Writes verbose diagnostics to the application log.'],
            ],
            'company' => [
                'company_name' => ['label' => 'Company name', 'type' => 'text', 'default' => 'iTrack Zimbabwe', 'max' => 150, 'help' => 'Printed on tax invoices.'],
                'company_address' => ['label' => 'Company address', 'type' => 'text', 'default' => '155 Longcheng Plaza, Belvedere, Harare', 'max' => 255],
                'company_phone' => ['label' => 'Contact number', 'type' => 'text', 'default' => '0773945200', 'max' => 50],
                'company_email' => ['label' => 'Email address', 'type' => 'email', 'default' => 'accounts@itrackzimbabwe.com', 'max' => 150],
                'company_tin' => ['label' => 'TIN number', 'type' => 'text', 'default' => '2000606958', 'max' => 50],
                'company_vat' => ['label' => 'VAT number', 'type' => 'text', 'default' => '220327466', 'max' => 50],
                'bank_name' => ['label' => 'Bank name', 'type' => 'text', 'default' => '', 'max' => 150],
                'bank_branch' => ['label' => 'Bank branch', 'type' => 'text', 'default' => '', 'max' => 150],
                'bank_account' => ['label' => 'Bank account number', 'type' => 'text', 'default' => '', 'max' => 100],
            ],
            'inventory' => [
                'low_stock_alerts_enabled' => ['label' => 'Enable low-stock alerts', 'type' => 'bool', 'default' => true, 'help' => 'Master switch for all low-stock warning behaviour.'],
                'default_reorder_level' => ['label' => 'Default reorder level', 'type' => 'int', 'default' => 5, 'min' => 0, 'max' => 100000, 'help' => 'Used for items that do not have their own reorder level. An alert fires when stock is at or below this number.'],
                'low_stock_show_dashboard' => ['label' => 'Show on dashboard', 'type' => 'bool', 'default' => true, 'help' => 'Display a low-stock alert banner on the dashboard.'],
                'low_stock_show_inventory' => ['label' => 'Show in inventory', 'type' => 'bool', 'default' => true, 'help' => 'Display a low-stock alert banner on the inventory page.'],
            ],
            'notifications' => [
                'mail_from' => ['label' => 'From email address', 'type' => 'email', 'default' => 'onboarding@resend.dev', 'max' => 150, 'help' => 'Sender for all Resend mail, including password resets and low-stock alerts. Use a domain verified in Resend; onboarding@resend.dev is for testing only.'],
                'low_stock_email_enabled' => ['label' => 'Email low-stock alerts', 'type' => 'bool', 'default' => false, 'help' => 'Send a summary email when items reach their reorder level.'],
                'low_stock_email_recipients' => ['label' => 'Alert recipients', 'type' => 'text', 'default' => '', 'max' => 500, 'help' => 'Comma-separated email addresses that receive low-stock summaries.'],
                'low_stock_email_interval_hours' => ['label' => 'Minimum hours between emails', 'type' => 'int', 'default' => 24, 'min' => 1, 'max' => 720, 'help' => 'Prevents repeated alerts on every page load.'],
                'low_stock_last_sent_at' => ['label' => 'Last alert sent', 'type' => 'readonly', 'default' => ''],
            ],
            'finance' => [
                'currency' => ['label' => 'Currency code', 'type' => 'text', 'default' => 'USD', 'max' => 10, 'help' => 'For example USD, ZWG or ZAR.'],
                'currency_symbol' => ['label' => 'Currency symbol', 'type' => 'text', 'default' => '$', 'max' => 5, 'help' => 'Prefix used when displaying money amounts.'],
                'default_vat_rate' => ['label' => 'Default VAT rate (%)', 'type' => 'float', 'default' => 15.0, 'min' => 0, 'max' => 100, 'help' => 'Prefilled when adding a new inventory item.'],
            ],
            'security' => [
                'password_min_length' => ['label' => 'Minimum password length', 'type' => 'int', 'default' => 12, 'min' => 8, 'max' => 64, 'help' => 'Applied to registration and password changes.'],
                'session_lifetime_minutes' => ['label' => 'Session lifetime (minutes)', 'type' => 'int', 'default' => 120, 'min' => 15, 'max' => 10080, 'help' => 'How long a sign-in session remains valid.'],
                'max_login_attempts' => ['label' => 'Failed login attempts allowed', 'type' => 'int', 'default' => 5, 'min' => 3, 'max' => 20, 'help' => 'Further attempts are blocked until the next successful sign-in.'],
            ],
        ];
    }

    public static function defaults(): array
    {
        $defaults = [];
        foreach (self::definitions() as $fields) {
            foreach ($fields as $key => $definition) {
                $defaults[$key] = $definition['default'];
            }
        }
        return $defaults;
    }

    public static function isWritable(string $key): bool
    {
        foreach (self::definitions() as $fields) {
            if (isset($fields[$key])) return $fields[$key]['type'] !== 'readonly';
        }
        return false;
    }

    public function all(): array
    {
        $values = self::defaults();
        foreach ($this->fetchAll('SELECT setting_key, setting_value FROM app_settings') as $row) {
            $values[(string) $row['setting_key']] = $row['setting_value'];
        }
        return $values;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $row = $this->fetchOne('SELECT setting_value FROM app_settings WHERE setting_key = :key', [':key' => $key]);
        return $row === null || $row['setting_value'] === null ? $default : $row['setting_value'];
    }

    /** Validate and persist only known keys, casting by the catalogue type. */
    public function save(array $input): void
    {
        $isSqlite = $this->db->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite';
        $sql = $isSqlite
            ? 'INSERT INTO app_settings (setting_key, setting_value, updated_at) VALUES (:key, :value, CURRENT_TIMESTAMP)
               ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value, updated_at = CURRENT_TIMESTAMP'
            : 'INSERT INTO app_settings (setting_key, setting_value, updated_at) VALUES (:key, :value, CURRENT_TIMESTAMP)
               ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = CURRENT_TIMESTAMP';
        $this->db->beginTransaction();
        try {
            foreach (self::definitions() as $fields) {
                foreach ($fields as $key => $definition) {
                    if ($definition['type'] === 'readonly' || !array_key_exists($key, $input)) continue;
                    $value = self::cast($key, $definition, $input[$key]);
                    $this->execute($sql, [':key' => $key, ':value' => $value]);
                }
            }
            $this->db->commit();
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $exception;
        }
    }

    public static function cast(string $key, array $definition, mixed $raw): string
    {
        $type = $definition['type'];
        if ($type === 'bool') return !empty($raw) && !in_array(strtolower((string) $raw), ['0', 'false', 'no', 'off', ''], true) ? '1' : '0';
        if ($type === 'int') {
            $value = filter_var($raw, FILTER_VALIDATE_INT);
            if ($value === false || $value < (int)($definition['min'] ?? PHP_INT_MIN) || $value > (int)($definition['max'] ?? PHP_INT_MAX)) {
                throw new InvalidArgumentException(($definition['label'] ?? $key) . ' must be a whole number in the allowed range.');
            }
            return (string) $value;
        }
        if ($type === 'float') {
            if (!is_numeric($raw)) throw new InvalidArgumentException(($definition['label'] ?? $key) . ' must be a number.');
            $value = (float) $raw;
            if ($value < (float)($definition['min'] ?? 0) || $value > (float)($definition['max'] ?? PHP_INT_MAX)) {
                throw new InvalidArgumentException(($definition['label'] ?? $key) . ' is outside the allowed range.');
            }
            return (string) round($value, 3);
        }
        $value = trim((string) $raw);
        if ($type === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(($definition['label'] ?? $key) . ' must be a valid email address.');
        }
        if (isset($definition['max']) && strlen($value) > (int) $definition['max']) {
            throw new InvalidArgumentException(($definition['label'] ?? $key) . ' is too long.');
        }
        return $value;
    }
}
