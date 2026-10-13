<?php $title = 'Inventory'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Inventory</h2>
    <a class="btn btn-primary" href="/index.php?controller=inventory&action=create"><i class="fa-solid fa-plus me-2" aria-hidden="true"></i>Add Device</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <p class="text-secondary small mb-3">Register each physical device individually using its unique IMEI.</p>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Device Type</th>
                        <th>IMEI</th>
                        <th>Unit</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Units</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['device_type'] ?? '—') ?></td>
                        <td><?= htmlspecialchars(($product['imei'] ?? '') !== '' ? $product['imei'] : 'IMEI not assigned') ?></td>
                        <td><?= htmlspecialchars($product['unit'] ?? '') ?></td>
                        <td>$<?= number_format((float) ($product['cost_price'] ?? 0), 2) ?></td>
                        <td>$<?= number_format((float) ($product['selling_price'] ?? 0), 2) ?></td>
                        <td><?= (int) ($product['opening_stock'] ?? 0) ?></td>
                        <td>
                            <a class="btn btn-sm btn-outline-secondary" href="/index.php?controller=inventory&action=edit&id=<?= (int) ($product['id'] ?? 0) ?>">Edit</a>
                            <a class="btn btn-sm btn-outline-danger" href="/index.php?controller=inventory&action=delete&id=<?= (int) ($product['id'] ?? 0) ?>" onclick="return confirm('Delete this product?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
