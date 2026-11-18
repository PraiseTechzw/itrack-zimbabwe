<?php
$settings = $settings ?? AppSetting::defaults();
$definitions = $definitions ?? AppSetting::definitions();
$flash = $flash ?? ['type' => '', 'message' => ''];
$config = $config ?? [];
$lowStockItems = $lowStockItems ?? [];
$esc = static fn($value) => htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
$groupTitles = [
    'general' => ['fa-sliders', 'General'],
    'company' => ['fa-building', 'Company details'],
    'inventory' => ['fa-boxes-stacked', 'Inventory & low-stock alerts'],
    'notifications' => ['fa-envelope', 'Notifications & email'],
    'finance' => ['fa-coins', 'Currency & VAT'],
    'security' => ['fa-shield-halved', 'Security & access'],
];
$renderField = static function (string $key, array $definition, array $settings, callable $esc): string {
    $type = $definition['type'];
    $value = (string)($settings[$key] ?? $definition['default']);
    $id = 'setting-' . $key;
    if ($type === 'readonly') {
        return '<div class="col-md-6"><label class="form-label" for="' . $esc($id) . '">' . $esc($definition['label']) . '</label>'
            . '<input class="form-control" id="' . $esc($id) . '" value="' . $esc($value ?: 'Not yet sent') . '" readonly>'
            . (isset($definition['help']) ? '<div class="form-text">' . $esc($definition['help']) . '</div>' : '') . '</div>';
    }
    if ($type === 'bool') {
        $checked = !in_array(strtolower($value), ['', '0', 'false', 'no', 'off'], true);
        return '<div class="col-md-6"><div class="form-check form-switch mt-4">'
            . '<input type="hidden" name="' . $esc($key) . '" value="0">'
            . '<input class="form-check-input" type="checkbox" id="' . $esc($id) . '" name="' . $esc($key) . '" value="1"' . ($checked ? ' checked' : '') . '>'
            . '<label class="form-check-label" for="' . $esc($id) . '">' . $esc($definition['label']) . '</label></div>'
            . (isset($definition['help']) ? '<div class="form-text">' . $esc($definition['help']) . '</div>' : '') . '</div>';
    }
    $htmlType = $type === 'email' ? 'email' : ($type === 'int' || $type === 'float' ? 'number' : 'text');
    $attributes = 'class="form-control" id="' . $esc($id) . '" name="' . $esc($key) . '" value="' . $esc($value) . '"';
    if (isset($definition['max'])) $attributes .= ' maxlength="' . (int)$definition['max'] . '"';
    if ($type === 'int') $attributes .= ' step="1" min="' . (int)($definition['min'] ?? 0) . '" max="' . (int)($definition['max'] ?? 100000) . '"';
    if ($type === 'float') $attributes .= ' step="0.001" min="' . (float)($definition['min'] ?? 0) . '" max="' . (float)($definition['max'] ?? 100) . '"';
    return '<div class="col-md-6"><label class="form-label" for="' . $esc($id) . '">' . $esc($definition['label']) . '</label>'
        . '<input type="' . $htmlType . '" ' . $attributes . '>'
        . (isset($definition['help']) ? '<div class="form-text">' . $esc($definition['help']) . '</div>' : '') . '</div>';
};
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div><div class="eyebrow mb-2">Administration</div><h1 class="page-title">Settings</h1><p class="page-subtitle">Configure the application, email alerts, company details and access defaults.</p></div>
    <a class="btn btn-outline-secondary" href="/index.php?controller=permissions"><i class="fa-solid fa-shield-halved me-2"></i>Manage permissions</a>
</div>
<?php if (!empty($flash['message'])): ?>
    <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : 'success' ?> d-flex align-items-center gap-2 mb-4" role="alert"><i class="fa-solid <?= $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?>"></i><span><?= $esc($flash['message']) ?></span></div>
<?php endif; ?>
<form method="post" action="/index.php?controller=settings&amp;action=save"><?= $this->csrfField() ?>
<?php foreach ($groupTitles as $group => [$icon, $heading]): if (!isset($definitions[$group])) continue; ?>
<section class="card shadow-sm border-0 mb-4" id="settings-<?= $esc($group) ?>">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex align-items-center gap-3 mb-3"><span class="module-icon"><i class="fa-solid <?= $esc($icon) ?>"></i></span><div><h2 class="h5 mb-0"><?= $esc($heading) ?></h2><p class="text-secondary small mb-0">Saved settings apply immediately across the application.</p></div></div>
        <div class="row g-3"><?php foreach ($definitions[$group] as $key => $definition): ?><?= $renderField($key, $definition, $settings, $esc) ?><?php endforeach; ?></div>
    </div>
</section>
<?php endforeach; ?>
<div class="d-flex flex-wrap gap-2 mb-5"><button class="btn btn-primary" type="submit"><i class="fa-solid fa-floppy-disk me-2"></i>Save all settings</button><a class="btn btn-outline-secondary" href="/index.php?controller=dashboard">Cancel</a></div>
</form>
<div class="row g-4 mb-4">
    <div class="col-lg-7">
        <section class="card shadow-sm border-0 h-100"><div class="card-body p-3 p-lg-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3"><div><h2 class="h5 mb-1">Low-stock watch</h2><p class="text-secondary small mb-0">Items at or below their effective reorder level (<?= (int)($settings['default_reorder_level'] ?? 5) ?> default).</p></div><span class="badge text-bg-warning"><?= count($lowStockItems) ?></span></div>
            <?php if (!$lowStockItems): ?><p class="text-secondary mb-0">No items are currently at or below their reorder level.</p><?php else: ?>
            <div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>Item</th><th>Category</th><th>In stock</th><th>Reorder at</th></tr></thead><tbody>
            <?php foreach ($lowStockItems as $item): ?><tr><td><?= $esc($item['item_type'] ?: $item['name']) ?><?= !empty($item['imei']) ? '<small class="d-block text-secondary">' . $esc($item['imei']) . '</small>' : '' ?></td><td><?= $esc($item['item_category'] ?? '—') ?></td><td><span class="badge text-bg-warning"><?= (int)$item['opening_stock'] ?></span></td><td><?= (int)$item['reorder_threshold'] ?><?= !empty($item['has_own_reorder_level']) ? '<small class="d-block text-secondary">item level</small>' : '' ?></td></tr><?php endforeach; ?>
            </tbody></table></div>
            <?php endif; ?>
            <form method="post" action="/index.php?controller=settings&amp;action=sendAlert" class="mt-3"><?= $this->csrfField() ?><button class="btn btn-sm btn-outline-primary" type="submit" <?= setting_bool('low_stock_email_enabled') ? '' : 'disabled' ?>><i class="fa-solid fa-envelope me-2"></i>Send alert email now</button><?php if (!setting_bool('low_stock_email_enabled')): ?><span class="small text-secondary ms-2">Enable email alerts above first.</span><?php endif; ?></form>
        </div></div>
    </div>
    <div class="col-lg-5">
        <section class="card shadow-sm border-0 h-100"><div class="card-body p-3 p-lg-4">
            <h2 class="h5 mb-3">System information</h2>
            <dl class="row mb-0 small"><dt class="col-sm-5">Application</dt><dd class="col-sm-7"><?= $esc(setting_string('app_name', 'iTrack Zimbabwe')) ?></dd><dt class="col-sm-5">Environment</dt><dd class="col-sm-7"><?= $esc($config['environment'] ?? 'development') ?></dd><dt class="col-sm-5">Database</dt><dd class="col-sm-7"><?= $esc($dbDriver ?? 'unknown') ?></dd><dt class="col-sm-5">PHP version</dt><dd class="col-sm-7"><?= $esc($phpVersion ?? PHP_VERSION) ?></dd><dt class="col-sm-5">Outgoing email</dt><dd class="col-sm-7"><?= !empty($config['resend_api_key']) ? 'configured' : 'not configured' ?></dd><dt class="col-sm-5">Storage</dt><dd class="col-sm-7"><code>storage/</code></dd></dl>
            <hr>
            <div class="d-grid gap-2"><a class="btn btn-sm btn-outline-secondary" href="/index.php?controller=permissions">Role permissions</a><a class="btn btn-sm btn-outline-secondary" href="/index.php?controller=reports">Reports & insights</a><a class="btn btn-sm btn-outline-secondary" href="https://github.com/PraiseTechzw/itrack-zimbabwe/wiki" target="_blank" rel="noopener">Documentation & wiki</a></div>
        </div></div>
    </div>
</div>
