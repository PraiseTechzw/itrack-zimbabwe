<div class="row g-4">
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Users</h6>
                <h2 class="fw-bold"><?= (int) ($summary['users'] ?? 0) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Products</h6>
                <h2 class="fw-bold"><?= (int) ($summary['products'] ?? 0) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Low Stock</h6>
                <h2 class="fw-bold"><?= (int) ($summary['low_stock'] ?? 0) ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="text-muted">Inventory Value</h6>
                <h2 class="fw-bold">$<?= number_format((float) ($summary['inventory_value'] ?? 0), 2) ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 card shadow-sm border-0">
    <div class="card-body">
        <h5 class="card-title">Quick Access</h5>
        <p class="text-muted mb-3">Open any module directly from the dashboard.</p>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=inventory">Inventory</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=client">Clients</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=supplier">Suppliers</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=accounting">Accounting</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=purchases">Purchases</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=sales">Sales</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=requisition">Requisitions</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=gps">GPS Devices</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=notification">Notifications</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=users">Users</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=reports">Reports</a>
            <a class="btn btn-secondary" href="/itrack-zimbabwe/public/index.php?controller=settings">Settings</a>
        </div>
    </div>
</div>
