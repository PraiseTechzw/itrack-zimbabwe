<div class="page-title">Welcome back, <?= htmlspecialchars(currentUser()['name'] ?? 'Administrator') ?>.</div>
<p class="page-subtitle">Your operations overview and module shortcuts are ready. Review the most important metrics and launch the sections available for your role.</p>
<div class="mb-4 d-flex flex-column flex-md-row gap-3 align-items-start align-items-md-center justify-content-between">
    <div>
        <span class="badge bg-primary text-uppercase mb-2">Role: <?= htmlspecialchars($role ?? 'Staff') ?></span>
        <div class="text-muted">Only the sections available to your role are shown in the sidebar and module cards below.</div>
    </div>
    <?php if (!empty($modules)): ?>
        <div class="text-muted">Available modules: <?= count($modules) ?></div>
    <?php endif; ?>
</div>
<div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 stats-grid">
    <div class="col">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Users</div>
                <div class="value"><?= (int) ($summary['users'] ?? 0) ?></div>
                <p class="text-muted mb-0">Total registered users on the platform.</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Products</div>
                <div class="value"><?= (int) ($summary['products'] ?? 0) ?></div>
                <p class="text-muted mb-0">Active inventory items available for sale.</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Clients</div>
                <div class="value"><?= (int) ($summary['clients'] ?? 0) ?></div>
                <p class="text-muted mb-0">Registered client accounts and organizations.</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">GPS Devices</div>
                <div class="value"><?= (int) ($summary['gps_devices'] ?? 0) ?></div>
                <p class="text-muted mb-0">Trackers currently managed by the system.</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Low Stock</div>
                <div class="value"><?= (int) ($summary['low_stock'] ?? 0) ?></div>
                <p class="text-muted mb-0">Products at or below reorder level.</p>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Inventory Value</div>
                <div class="value">$<?= number_format((float) ($summary['inventory_value'] ?? 0), 2) ?></div>
                <p class="text-muted mb-0">Estimated stock value at cost price.</p>
            </div>
        </div>
    </div>
</div>

<div class="dashboard-banner mt-4 p-4">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h5>Launch the sections you need</h5>
            <p class="mb-0">Your dashboard adapts to your role: only the modules you can access are shown here.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <?php $defaultModule = $modules[0]['route'] ?? 'dashboard'; ?>
            <a class="btn btn-brand btn-primary" href="<?= $defaultModule === 'dashboard' ? '/itrack-zimbabwe/public/dashboard.php' : '/itrack-zimbabwe/public/index.php?controller=' . urlencode($defaultModule) ?>">Open <?= htmlspecialchars($modules[0]['label'] ?? 'Dashboard') ?></a>
        </div>
    </div>
</div>

<div class="card mt-4 border-0">
    <div class="card-body">
        <h5 class="card-title">Your available modules</h5>
        <div class="row g-3 mt-3">
            <?php foreach ($modules as $module): ?>
                <div class="col-sm-6 col-lg-4">
                    <a class="card text-decoration-none h-100" href="/itrack-zimbabwe/public/index.php?controller=<?= htmlspecialchars($module['route']) ?>">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary rounded-pill me-2"><i class="fa-solid <?= htmlspecialchars($module['icon']) ?>"></i></span>
                                <h6 class="mb-0 text-dark"><?= htmlspecialchars($module['label']) ?></h6>
                            </div>
                            <p class="text-muted mb-0">Open <?= htmlspecialchars($module['label']) ?> and manage your assigned workflows.</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
