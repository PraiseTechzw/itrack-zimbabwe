<?php $title = $mode === 'edit' ? 'Edit Product' : 'Create Product'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit Product' : 'Create Product' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($product['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($product['sku'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= htmlspecialchars((string) ($product['cost_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= htmlspecialchars((string) ($product['selling_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Opening Stock</label>
                        <input type="number" name="opening_stock" class="form-control" value="<?= htmlspecialchars((string) ($product['opening_stock'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reorder Level</label>
                        <input type="number" name="reorder_level" class="form-control" value="<?= htmlspecialchars((string) ($product['reorder_level'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save Product</button>
                        <a class="btn btn-outline-secondary ms-2" href="/itrack-zimbabwe/public/index.php?controller=inventory">Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
