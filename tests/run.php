<?php

/**
 * Lightweight regression suite for the operations workflow changes.
 * Run with: php tests/run.php
 */

$database = sys_get_temp_dir() . '/itrack-tests-' . getmypid() . '.sqlite';
@unlink($database);
putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=' . $database);
putenv('APP_ENV=production'); // skip the development seed data
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_SQLITE_PATH'] = $database;
$_ENV['APP_ENV'] = 'production';

require_once dirname(__DIR__) . '/app/models/Product.php';
require_once dirname(__DIR__) . '/app/models/GPSDevice.php';
require_once dirname(__DIR__) . '/app/models/Report.php';
require_once dirname(__DIR__) . '/app/services/XlsxReport.php';

$failures = 0;
function check(string $name, bool $condition): void
{
    global $failures;
    if ($condition) {
        echo "PASS  {$name}\n";
        return;
    }
    $failures++;
    echo "FAIL  {$name}\n";
}

function throws(callable $callback): bool
{
    try {
        $callback();
        return false;
    } catch (InvalidArgumentException) {
        return true;
    }
}

$products = new Product();

// Identifier rules: SIM lines are capped at 10 digits, hard-drive serials are alphanumeric.
check('10-digit SIM accepted', Product::identifierError('Sim cards', '0771234567') === null);
check('11-digit SIM rejected', Product::identifierError('Sim cards', '07712345678') !== null);
check('non-numeric SIM rejected', Product::identifierError('Sim cards', '077abc4567') !== null);
check('hard drive serial accepts letters and numbers', Product::identifierError('Hard drives', 'WD-WX12AB34') === null);
check('FLS serial stays numeric only', Product::identifierError('FLS', 'AB123') !== null);
check('VAT rate defaults to 15', Product::vatRate(null) === 15.0);
check('VAT rate is bounded', throws(static fn() => Product::vatRate(150)));

$trackerId = (int) $products->create([
    'item_category' => 'Tracker', 'item_type' => 'Teltonika FMB920',
    'identifier' => '123456789012345', 'quantity' => 2, 'vat_rate' => 15,
]);
$flsId = (int) $products->create([
    'item_category' => 'FLS', 'item_type' => 'Escort', 'identifier' => '111222', 'quantity' => 1,
]);
$cameraId = (int) $products->create([
    'item_category' => 'Cameras', 'item_type' => 'IVMS', 'identifier' => '333444', 'quantity' => 1,
]);
$hardDriveId = (int) $products->create([
    'item_category' => 'Hard drives', 'item_type' => 'Hard drive', 'identifier' => 'HD-AB12CD', 'quantity' => 1,
]);
$simId = (int) $products->create([
    'item_category' => 'Sim cards', 'item_type' => 'Econet', 'identifier' => '0771234567', 'quantity' => 1,
]);

check('VAT rate persisted', (float) $products->find($trackerId)['vat_rate'] === 15.0);

$gps = new GPSDevice();

// Camera-only installation: device SIM is ignored/rejected, hard drive is linked.
$installationId = $gps->create([
    'package_type' => 'Camera only',
    'camera_product_id' => $cameraId,
    'hard_drive_product_id' => $hardDriveId,
    'camera_sim_product_id' => $simId,
    'sim_product_id' => 0,
    'installed_at' => date('Y-m-d'),
    'client_id' => 0, 'installer_id' => 0, 'support_id' => 0,
]);
$installation = $gps->find((int) $installationId);
check('camera installation stored hard drive serial', ($installation['hard_drive_serial'] ?? '') === 'HD-AB12CD');
check('camera installation stored hard drive product id', (int)($installation['hard_drive_product_id'] ?? 0) === $hardDriveId);
check('camera installation has no device SIM', trim((string)($installation['sim_number'] ?? '')) === '');
check('camera SIM assigned', trim((string)($installation['camera_sim_number'] ?? '')) === '0771234567');
check('hard drive stock consumed', (int)$products->find($hardDriveId)['opening_stock'] === 0);
check('camera stock consumed', (int)$products->find($cameraId)['opening_stock'] === 0);

check('camera-only device SIM rejected by model', throws(static fn() => $gps->create([
    'package_type' => 'Camera only', 'camera_product_id' => $cameraId,
    'sim_product_id' => $simId, 'installed_at' => date('Y-m-d'),
])));

// A second installation with a tracker and FLS exercises the installed-quantity aggregate.
$gps->create([
    'package_type' => 'Basic + FLS',
    'tracker_product_id' => $trackerId,
    'fls_product_id' => $flsId,
    'installed_at' => date('Y-m-d'),
    'client_id' => 0, 'installer_id' => 0, 'support_id' => 0,
]);
$installed = $products->installedQuantities();
check('installed quantity counts camera once', ($installed[$cameraId] ?? 0) === 1);
check('installed quantity counts hard drive once', ($installed[$hardDriveId] ?? 0) === 1);
check('installed quantity counts tracker once', ($installed[$trackerId] ?? 0) === 1);

// Deinstallation: chassis optional, specific camera picked from the installation.
$options = $gps->activeEquipmentForDeinstallation();
check('equipment picker lists the camera', count(array_filter($options, static fn(array $o): bool => $o['item'] === 'camera')) === 1);
check('equipment picker excludes device SIM for camera-only', count(array_filter($options, static fn(array $o): bool => $o['item'] === 'tracker' && (int)explode(':', $o['ref'])[0] === (int)$installationId)) === 0);

$gps->deinstall((int) $installationId, [
    'registration_number' => 'ABC123',
    'chassis_number' => '',
    'removed_at' => date('Y-m-d'),
    'reason' => 'Test removal',
], 0, 'camera');

$history = $gps->deinstallations();
check('deinstallation recorded', count($history) === 1);
check('removed item recorded as camera', ($history[0]['removed_item'] ?? '') === 'camera');
check('empty chassis accepted', trim((string)($history[0]['chassis_number'] ?? '')) === '');
check('hard drive serial recorded on deinstallation', ($history[0]['hard_drive_serial'] ?? '') === 'HD-AB12CD');
check('camera stock restored', (int)$products->find($cameraId)['opening_stock'] === 1);
check('hard drive stock restored', (int)$products->find($hardDriveId)['opening_stock'] === 1);
check('installation marked deinstalled', ($gps->find((int)$installationId)['status'] ?? '') === 'deinstalled');
check('deinstalled unit excluded from installed quantity', ($products->installedQuantities()[$cameraId] ?? 0) === 0);

// Demand forecast context is database-backed and aggregated.
$report = new Report();
$db = new PDO('sqlite:' . $database);
$db->exec("INSERT INTO requisitions (requested_by, department, purpose, requested_date, status, request_type, installation_items, created_at) VALUES (1, 'IT', 'Test', '" . date('Y-m-d') . "', 'approved', 'installation', '" . json_encode([
    'device' => ['quantity' => 3], 'camera' => ['quantity' => 2], 'fls' => ['quantity' => 1], 'sim_line' => ['quantity' => 0],
]) . "', CURRENT_TIMESTAMP)");
$insights = $report->aiInsightsData();
check('demand forecast present', isset($insights['demand_forecast']['recent_30_days']));
check('requested tracker quantity aggregated', ($insights['demand_forecast']['recent_30_days']['requested_quantities']['Tracker'] ?? 0) === 3);
check('requested camera quantity aggregated', ($insights['demand_forecast']['recent_30_days']['requested_quantities']['Cameras'] ?? 0) === 2);
check('fulfilled demand present', is_array($insights['demand_forecast']['recent_30_days']['installed_by_category'] ?? null));

// Excel export contains the new columns.
$workbook = (new XlsxReport())->build([
    ['quantity_in_stock' => 1, 'quantity_installed' => 2, 'vat_rate' => 15.0, 'selling_price_incl_vat' => 23.0],
], 'Inventory');
check('xlsx generated', strlen($workbook) > 100);

// Outgoing email is built for Resend and validated before any network call.
require_once dirname(__DIR__) . '/app/services/ResendMailer.php';
$resendPayload = ResendMailer::payload('ops@example.com', 'no-reply@example.com', 'Low stock', "Line one\nLine two");
check('resend payload has from/to/subject/text', is_array($resendPayload)
    && $resendPayload['from'] === 'no-reply@example.com'
    && $resendPayload['to'] === ['ops@example.com']
    && $resendPayload['subject'] === 'Low stock'
    && str_contains($resendPayload['text'], 'Line two'));
check('resend payload rejects invalid recipient', ResendMailer::payload('not-an-email', 'no-reply@example.com', 'Subject', 'Body') === false);
check('resend payload rejects header injection', ResendMailer::payload('ops@example.com', "no-reply@example.com\nBcc: evil@example.com", 'Subject', 'Body') === false);
check('resend send fails without an API key', ResendMailer::send('ops@example.com', 'Subject', 'Body', 'no-reply@example.com', '') === false);
check('resend failure exposes a useful hint', str_contains(ResendMailer::lastError(), 'RESEND_API_KEY'));

@unlink($database);
echo $failures === 0 ? "\nAll checks passed.\n" : "\n{$failures} check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
