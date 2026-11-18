<?php

/** Regression checks for persisted settings and low-stock alert thresholds. */

$database = sys_get_temp_dir() . '/itrack-settings-tests-' . getmypid() . '.sqlite';
@unlink($database);
putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=' . $database);
putenv('APP_ENV=production');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_SQLITE_PATH'] = $database;
$_ENV['APP_ENV'] = 'production';

require_once dirname(__DIR__) . '/app/models/AppSetting.php';
require_once dirname(__DIR__) . '/app/models/Product.php';
require_once dirname(__DIR__) . '/app/helpers/settings.php';
require_once dirname(__DIR__) . '/app/services/LowStockAlerter.php';

$failures = 0;
function check(string $name, bool $condition): void
{
    global $failures;
    echo ($condition ? 'PASS  ' : 'FAIL  ') . $name . "\n";
    if (!$condition) $failures++;
}

$settings = new AppSetting();

check('default reorder level is 5', (int) $settings->all()['default_reorder_level'] === 5);
check('alerts enabled by default', in_array($settings->all()['low_stock_alerts_enabled'], ['1', 1, true], true));
check('currency defaults to USD', ($settings->all()['currency'] ?? '') === 'USD');

$settings->save([
    'default_reorder_level' => '4',
    'low_stock_alerts_enabled' => '0',
    'low_stock_email_recipients' => 'ops@example.com, bad-address',
    'app_name' => 'Fleet Ops',
    'default_vat_rate' => '15.5',
]);
check('integer setting saved', (int) $settings->all()['default_reorder_level'] === 4);
check('boolean setting saved', setting_bool('low_stock_alerts_enabled') === false);
check('app name saved', setting_string('app_name') === 'Fleet Ops');
check('float setting saved', setting_float('default_vat_rate') === 15.5);
check('unknown keys ignored', !array_key_exists('not_a_setting', $settings->all()));

try {
    $settings->save(['default_reorder_level' => '-2']);
    check('invalid integer rejected', false);
} catch (InvalidArgumentException) {
    check('invalid integer rejected', true);
}

check('recipient list filters invalid emails', LowStockAlerter::recipients('ops@example.com, bad-address') === ['ops@example.com']);
$settings->save(['low_stock_email_enabled' => '1', 'low_stock_email_interval_hours' => '24']);
check('email gating requires valid recipients and enabled flag', LowStockAlerter::shouldSend($settings->all()) === true);
$settings->save(['low_stock_email_enabled' => '0']);
check('email gating respects disabled flag', LowStockAlerter::shouldSend($settings->all()) === false);

$products = new Product();
$products->create(['item_category' => 'Tracker', 'item_type' => 'Teltonika FMB920', 'identifier' => '111', 'quantity' => 2, 'reorder_level' => 0]);
$products->create(['item_category' => 'Tracker', 'item_type' => 'Galileosky 7x', 'identifier' => '222', 'quantity' => 2, 'reorder_level' => 3]);
$products->create(['item_category' => 'Cameras', 'item_type' => 'IVMS', 'identifier' => '333', 'quantity' => 9, 'reorder_level' => 0]);
$all = $products->all();
check('reorder level persisted', array_column($all, 'reorder_level') === [0, 3, 0]);

$low = $products->lowStockItems(4);
$labels = array_column($low, 'item_type');
check('global threshold applied to item without own level', in_array('Teltonika FMB920', $labels, true));
check('own reorder level overrides global threshold', in_array('Galileosky 7x', $labels, true));
check('healthy item excluded', !in_array('IVMS', $labels, true));
$byType = [];
foreach ($low as $item) $byType[(string) $item['item_type']] = $item;
check('global threshold reported for default item', (int) ($byType['Teltonika FMB920']['reorder_threshold'] ?? 0) === 4);
check('own-level flag reported for overridden item', (bool) ($byType['Galileosky 7x']['has_own_reorder_level'] ?? false) === true);
check('own threshold reported for overridden item', (int) ($byType['Galileosky 7x']['reorder_threshold'] ?? 0) === 3);
check('low stock count uses effective threshold', $products->lowStockCount(4) === 2);

$settings->save(['low_stock_last_sent_at' => date('c')]);
check('throttle blocks immediate resend', LowStockAlerter::shouldSend($settings->all()) === false);

$priorRegistrationEnv = $_ENV['APP_ALLOW_REGISTRATION'] ?? null;
$_ENV['APP_ALLOW_REGISTRATION'] = 'false';
check('registration default respects explicit env override', AppSetting::defaults()['allow_registration'] === false);
if ($priorRegistrationEnv === null) unset($_ENV['APP_ALLOW_REGISTRATION']); else $_ENV['APP_ALLOW_REGISTRATION'] = $priorRegistrationEnv;
$settings->save(['allow_registration' => '1']);
check('registration can be enabled in settings', (string) $settings->all()['allow_registration'] === '1');
$settings->save(['allow_registration' => '0']);
check('registration can be hidden in settings', (string) $settings->all()['allow_registration'] === '0');

@unlink($database);
echo $failures === 0 ? "\nAll settings checks passed.\n" : "\n{$failures} settings check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
