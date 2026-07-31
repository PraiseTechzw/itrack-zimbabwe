<div class="page-title">Welcome back, <?= htmlspecialchars(currentUser()['name'] ?? 'Administrator') ?>.</div>
<p class="page-subtitle">Your operations overview and module shortcuts are ready. Review the most important metrics and launch any section at a glance.</p>

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
            <h5>Launch modules in one click</h5>
            <p class="mb-0">Use the buttons below to jump straight to the most important areas of the system.</p>
        </div>
        <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
            <a class="btn btn-brand btn-primary" href="/itrack-zimbabwe/public/index.php?controller=inventory">Open Inventory</a>
        </div>
    </div>
</div>

<div class="card mt-4 border-0">
    <div class="card-body">
        <h5 class="card-title">Module shortcuts</h5>
        <div class="d-flex flex-wrap quick-actions">
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=inventory">Inventory</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=clients">Clients</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=supplier">Suppliers</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=users">Users</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=gps">GPS Devices</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=accounting">Accounting</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=purchases">Purchases</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=sales">Sales</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=requisition">Requisitions</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=reports">Reports</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=notification">Notifications</a>
            <a class="btn btn-outline-primary" href="/itrack-zimbabwe/public/index.php?controller=settings">Settings</a>
        </div>
    </div>
</div>
