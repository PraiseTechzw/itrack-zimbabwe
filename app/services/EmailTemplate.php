<?php

/**
 * Branded, email-client-safe HTML templates with a plain-text alternative.
 *
 * Layouts use tables and inline styles (no external CSS, images or scripts) so
 * they render consistently in Gmail, Outlook and mobile mail clients.
 */
class EmailTemplate
{
    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /** Shared shell: dark brand header, white content card, muted footer. */
    private static function wrap(string $appName, string $subject, string $eyebrow, string $preheader, string $body, string $text): array
    {
        $app = self::e($appName);
        $subjectHtml = self::e($subject);
        $year = date('Y');
        $html = '<!doctype html>' . "\n"
            . '<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>' . $subjectHtml . '</title>'
            . '<style>@media only screen and (max-width:620px){.container{width:100%!important;max-width:100%!important}.px{padding-left:22px!important;padding-right:22px!important}}</style>'
            . '</head><body style="margin:0;padding:0;background:#f4f7fb;">'
            . '<div style="display:none;max-height:0;overflow:hidden;opacity:0;mso-hide:all;">' . self::e($preheader) . '</div>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f4f7fb;padding:32px 12px;">'
            . '<tr><td align="center">'
            . '<table role="presentation" class="container" width="600" cellpadding="0" cellspacing="0" border="0" style="width:600px;max-width:600px;background:#ffffff;border-radius:14px;overflow:hidden;">'
            . '<tr><td style="background:#0b1324;padding:24px 32px;">'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>'
            . '<td width="36" style="width:36px;height:36px;border-radius:10px;background:#4f46e5;color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:700;text-align:center;vertical-align:middle;">iT</td>'
            . '<td style="padding-left:12px;color:#f8fbff;font-family:Arial,sans-serif;font-size:15px;font-weight:700;letter-spacing:.02em;">' . $app . '</td>'
            . '</tr></table></td></tr>'
            . '<tr><td class="px" style="padding:34px 32px 8px;">'
            . '<p style="margin:0 0 10px;color:#4f46e5;font-family:Arial,sans-serif;font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;">' . self::e($eyebrow) . '</p>'
            . $body
            . '</td></tr>'
            . '<tr><td class="px" style="padding:18px 32px 34px;">'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #e6ebf2;"><tr><td style="padding-top:20px;color:#738096;font-family:Arial,sans-serif;font-size:12px;line-height:1.6;">'
            . 'You received this email because it was sent to your account on ' . $app . '. If you no longer expect these messages, contact your administrator.'
            . '</td></tr></table></td></tr>'
            . '<tr><td style="background:#f8fafc;border-top:1px solid #e6ebf2;padding:18px 32px;color:#8a96a8;font-family:Arial,sans-serif;font-size:11px;line-height:1.6;">'
            . $app . ' &middot; Automated message &middot; &copy; ' . $year
            . '</td></tr>'
            . '</table>'
            . '<p style="margin:18px 0 0;color:#8a96a8;font-family:Arial,sans-serif;font-size:11px;">Please do not reply to this address.</p>'
            . '</td></tr></table></body></html>';

        return ['subject' => $subject, 'html' => $html, 'text' => $text];
    }

    public static function passwordReset(string $appName, string $resetUrl, int $expiresMinutes = 60): array
    {
        $app = trim($appName) !== '' ? trim($appName) : 'iTrack Zimbabwe';
        $expiresMinutes = max(1, $expiresMinutes);
        $url = self::e($resetUrl);
        $subject = 'Reset your ' . $app . ' password';

        $body = '<h1 style="margin:0 0 14px;color:#0b1324;font-family:Arial,sans-serif;font-size:25px;line-height:1.25;font-weight:700;">Reset your password</h1>'
            . '<p style="margin:0 0 14px;color:#526076;font-family:Arial,sans-serif;font-size:14px;line-height:1.7;">Hello,</p>'
            . '<p style="margin:0;color:#526076;font-family:Arial,sans-serif;font-size:14px;line-height:1.7;">We received a request to reset your ' . self::e($app) . ' password. Use the button below to choose a new one. The link expires in ' . $expiresMinutes . ' minutes.</p>'
            . '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:26px 0 10px;"><tr><td style="border-radius:8px;background:#4f46e5;">'
            . '<a href="' . $url . '" style="display:inline-block;padding:14px 26px;border-radius:8px;background:#4f46e5;color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:700;text-decoration:none;">Reset password</a>'
            . '</td></tr></table>'
            . '<p style="margin:0;color:#738096;font-family:Arial,sans-serif;font-size:12px;line-height:1.6;">If the button does not work, copy this link into your browser:<br><a href="' . $url . '" style="color:#4f46e5;word-break:break-all;">' . $url . '</a></p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:24px;background:#f8fafc;border-left:3px solid #c7c3ff;"><tr><td style="padding:14px 16px;color:#526076;font-family:Arial,sans-serif;font-size:13px;line-height:1.6;">If you did not request a reset, you can safely ignore this email. Your password will not change.</td></tr></table>';

        $text = "Hello,\n\nWe received a request to reset your {$app} password. Use the link below to choose a new one. It expires in {$expiresMinutes} minutes.\n\n"
            . "{$resetUrl}\n\nIf you did not request a reset, you can safely ignore this email. Your password will not change.\n";

        return self::wrap($app, $subject, 'Account security', 'Reset your password within ' . $expiresMinutes . ' minutes.', $body, $text);
    }

    /** Low-stock summary with a compact item table and a review-inventory link. */
    public static function lowStock(string $appName, array $items, string $inventoryUrl = ''): array
    {
        $app = trim($appName) !== '' ? trim($appName) : 'iTrack Zimbabwe';
        $count = count($items);
        $subject = $app . ': low-stock alert (' . $count . ' item' . ($count === 1 ? '' : 's') . ')';

        $rows = '';
        $textLines = [];
        $limit = 20;
        foreach (array_slice($items, 0, $limit) as $item) {
            $label = trim((string) ($item['item_type'] ?? $item['name'] ?? 'Item'));
            $serial = trim((string) ($item['imei'] ?? ''));
            $stock = (int) ($item['opening_stock'] ?? 0);
            $reorder = (int) ($item['reorder_threshold'] ?? 0);
            $display = $label . ($serial !== '' ? ' [' . $serial . ']' : '');
            $rows .= '<tr><td style="padding:11px 0;border-bottom:1px solid #eef1f5;color:#0b1324;font-family:Arial,sans-serif;font-size:13px;font-weight:600;">' . self::e($display) . '</td>'
                . '<td align="right" style="padding:11px 0;border-bottom:1px solid #eef1f5;color:' . ($stock <= $reorder ? '#b45309' : '#526076') . ';font-family:Arial,sans-serif;font-size:13px;font-weight:700;white-space:nowrap;">' . $stock . '</td>'
                . '<td align="right" style="padding:11px 0;border-bottom:1px solid #eef1f5;color:#738096;font-family:Arial,sans-serif;font-size:13px;white-space:nowrap;">' . $reorder . '</td></tr>';
            $textLines[] = '- ' . $display . ': ' . $stock . ' in stock (reorder at ' . $reorder . ')';
        }
        if ($count > $limit) {
            $rows .= '<tr><td colspan="3" style="padding:11px 0;color:#738096;font-family:Arial,sans-serif;font-size:12px;">and ' . ($count - $limit) . ' more item' . ($count - $limit === 1 ? '' : 's') . '.</td></tr>';
            $textLines[] = '- and ' . ($count - $limit) . ' more item' . ($count - $limit === 1 ? '' : 's') . '.';
        }

        $body = '<h1 style="margin:0 0 14px;color:#0b1324;font-family:Arial,sans-serif;font-size:25px;line-height:1.25;font-weight:700;">Low-stock alert</h1>'
            . '<p style="margin:0 0 18px;color:#526076;font-family:Arial,sans-serif;font-size:14px;line-height:1.7;">' . $count . ' inventory item' . ($count === 1 ? '' : 's') . ' reached ' . ($count === 1 ? 'its' : 'their') . ' reorder level and need your attention.</p>'
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border-collapse:collapse;">'
            . '<tr><th align="left" style="padding:0 0 8px;border-bottom:2px solid #e6ebf2;color:#8a96a8;font-family:Arial,sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Item</th>'
            . '<th align="right" style="padding:0 0 8px;border-bottom:2px solid #e6ebf2;color:#8a96a8;font-family:Arial,sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">In stock</th>'
            . '<th align="right" style="padding:0 0 8px;border-bottom:2px solid #e6ebf2;color:#8a96a8;font-family:Arial,sans-serif;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;">Reorder at</th></tr>'
            . $rows . '</table>'
            . '<p style="margin:20px 0 0;color:#526076;font-family:Arial,sans-serif;font-size:14px;line-height:1.7;">Please review inventory and raise a purchase or requisition as needed.</p>';

        if ($inventoryUrl !== '') {
            $url = self::e($inventoryUrl);
            $body .= '<table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin:22px 0 0;"><tr><td style="border-radius:8px;background:#4f46e5;">'
                . '<a href="' . $url . '" style="display:inline-block;padding:13px 24px;border-radius:8px;background:#4f46e5;color:#ffffff;font-family:Arial,sans-serif;font-size:14px;font-weight:700;text-decoration:none;">Review inventory</a>'
                . '</td></tr></table>';
        }

        $text = "The following inventory items have reached their reorder level:\n\n" . implode("\n", $textLines)
            . "\n\nPlease review inventory and raise a purchase or requisition as needed.\n";
        if ($inventoryUrl !== '') $text .= "\nReview inventory: {$inventoryUrl}\n";

        return self::wrap($app, $subject, 'Inventory', $count . ' item' . ($count === 1 ? '' : 's') . ' reached their reorder level.', $body, $text);
    }
}
