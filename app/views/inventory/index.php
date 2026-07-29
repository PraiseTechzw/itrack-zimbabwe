<?php $title = 'Inventory'; ?>
<h2 class="mb-4">Inventory</h2>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Unit</th>
                        <th>Cost Price</th>
                        <th>Selling Price</th>
                        <th>Opening Stock</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= htmlspecialchars($product['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($product['sku'] ?? '') ?></td>
                        <td><?= htmlspecialchars($product['unit'] ?? '') ?></td>
                        <td>$<?= number_format((float) ($product['cost_price'] ?? 0), 2) ?></td>
                        <td>$<?= number_format((float) ($product['selling_price'] ?? 0), 2) ?></td>
                        <td><?= (int) ($product['opening_stock'] ?? 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
