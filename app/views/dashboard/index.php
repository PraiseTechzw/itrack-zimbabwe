<?php
$userName = currentUser()['name'] ?? 'Administrator';
$firstName = explode(' ', trim($userName))[0];
$defaultModule = $modules[0]['route'] ?? 'dashboard';
$defaultLabel = $modules[0]['label'] ?? 'Dashboard';
$metricCards = [
    ['label'=>'Total users','value'=>(int)($summary['users']??0),'meta'=>'Active platform accounts','icon'=>'fa-users','tone'=>'indigo'],
    ['label'=>'Products tracked','value'=>(int)($summary['products']??0),'meta'=>'Across your inventory','icon'=>'fa-boxes-stacked','tone'=>'blue'],
    ['label'=>'Client accounts','value'=>(int)($summary['clients']??0),'meta'=>'Customer organizations','icon'=>'fa-handshake-angle','tone'=>'mint'],
    ['label'=>'GPS devices','value'=>(int)($summary['gps_devices']??0),'meta'=>'Trackers being managed','icon'=>'fa-location-dot','tone'=>'amber'],
    ['label'=>'Low-stock alerts','value'=>(int)($summary['low_stock']??0),'meta'=>'Need replenishment review','icon'=>'fa-triangle-exclamation','tone'=>'rose'],
    ['label'=>'Inventory value','value'=>'$'.number_format((float)($summary['inventory_value']??0),2),'meta'=>'Estimated cost value','icon'=>'fa-chart-line','tone'=>'violet'],
];
$quickActions = [
    ['label'=>'Add inventory','route'=>'inventory','icon'=>'fa-plus'],
    ['label'=>'New purchase','route'=>'purchases','icon'=>'fa-cart-shopping'],
    ['label'=>'Create sale','route'=>'sales','icon'=>'fa-arrow-trend-up'],
    ['label'=>'New requisition','route'=>'requisition','icon'=>'fa-clipboard-list'],
];
?>
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-end gap-3 mb-4">
    <div><div class="eyebrow mb-2">Operations overview</div><h1 class="page-title">Good morning, <?= htmlspecialchars($firstName) ?>.</h1><p class="page-subtitle">Here’s what’s happening across your Zimbabwe operations today.</p></div>
    <div class="d-flex gap-2"><a class="btn btn-light" href="/index.php?controller=reports"><i class="fa-solid fa-file-lines me-2"></i>View reports</a><a class="btn btn-primary" href="<?= $defaultModule === 'dashboard' ? '/dashboard.php' : '/index.php?controller=' . urlencode($defaultModule) ?>"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i>Open <?= htmlspecialchars($defaultLabel) ?></a></div>
</div>
<div class="card banner mb-4"><div class="card-body p-4 p-lg-4"><div class="row align-items-center"><div class="col-lg-8"><div class="eyebrow" style="color:#bfc1ff">Command centre</div><h2 class="h4 fw-bold mt-2 mb-1">Your operation is in view.</h2><p>Monitor stock, movement, procurement, and field activity from one connected workspace.</p></div><div class="col-lg-4 text-lg-end mt-3 mt-lg-0"><span class="status status-success" style="background:rgba(255,255,255,.14);color:#fff"><span class="status-dot"></span>All systems operational</span></div></div></div></div>
<div class="stats-grid mb-4">
    <?php foreach ($metricCards as $metric): ?><div class="card metric-card"><div class="card-body"><div class="metric-head"><span class="metric-label"><?= htmlspecialchars($metric['label']) ?></span><span class="metric-icon"><i class="fa-solid <?= htmlspecialchars($metric['icon']) ?>"></i></span></div><div class="metric-value"><?= htmlspecialchars((string)$metric['value']) ?></div><div class="metric-meta"><i class="fa-solid fa-arrow-trend-up me-1" style="color:#13b981"></i><?= htmlspecialchars($metric['meta']) ?></div></div></div><?php endforeach; ?>
</div>
<div class="dashboard-grid mb-4">
    <section class="card"><div class="card-body"><div class="section-heading"><div><div class="eyebrow">Start a workflow</div><h2 class="mt-1">Quick actions</h2></div><i class="fa-solid fa-bolt" style="color:#f59e0b"></i></div><div class="quick-grid"><?php foreach($quickActions as $action): ?><a class="quick-action" href="/index.php?controller=<?= urlencode($action['route']) ?>"><i class="fa-solid <?= htmlspecialchars($action['icon']) ?>"></i><span><?= htmlspecialchars($action['label']) ?></span><i class="fa-solid fa-arrow-up-right-from-square ms-auto" style="font-size:10px;color:#a7b1c2"></i></a><?php endforeach; ?></div></div></section>
    <section class="card"><div class="card-body"><div class="section-heading"><div><div class="eyebrow">Workspace access</div><h2 class="mt-1">Your role</h2></div><span class="status status-success">Active</span></div><div class="d-flex align-items-center gap-3"><div class="module-icon"><i class="fa-solid fa-shield-halved"></i></div><div><div class="fw-bold"><?= htmlspecialchars($role ?? 'Staff') ?></div><div class="metric-meta"><?= count($modules ?? []) ?> modules available to you</div></div></div></div></section>
</div>
<section><div class="section-heading"><div><div class="eyebrow">Your workspace</div><h2 class="mt-1">Launch a module</h2></div><span class="metric-label"><?= count($modules ?? []) ?> available</span></div><div class="row g-3"><?php foreach ($modules as $module): ?><div class="col-sm-6 col-xl-3"><a class="card module-card d-block" href="<?= $module['route'] === 'dashboard' ? '/dashboard.php' : '/index.php?controller=' . urlencode($module['route']) ?>"><div class="card-body"><div class="d-flex justify-content-between align-items-start"><span class="module-icon"><i class="fa-solid <?= htmlspecialchars($module['icon']) ?>"></i></span><i class="fa-solid fa-arrow-up-right-from-square" style="font-size:11px;color:#a7b1c2"></i></div><h3><?= htmlspecialchars($module['label']) ?></h3><p>Manage <?= strtolower(htmlspecialchars($module['label'])) ?> workflows and stay on top of your operations.</p></div></a></div><?php endforeach; ?></div></section>
