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
        <h5 class="card-title">Operations</h5>
        <p class="text-muted mb-3">Manage core ERP activities from one place.</p>
        <a class="btn btn-primary me-2" href="/itrack-zimbabwe/public/index.php?controller=inventory">Open Inventory</a>
        <a class="btn btn-outline-secondary" href="/itrack-zimbabwe/public/index.php?controller=inventory&action=create">Create Product</a>
    </div>
</div>
