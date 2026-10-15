<?php $mode = $mode ?? 'create'; $product = $product ?? []; $title = $mode === 'edit' ? 'Edit Product' : 'Create Product'; ?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit Product' : 'Create Product' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <p class="text-secondary small mb-4"><?= $mode === 'edit' ? 'Each physical device must have its own unique IMEI.' : 'Add one device or paste multiple unique IMEIs. We will create a separate inventory record for each device.' ?></p>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label" for="product-device-type">Device Type</label>
                        <select id="product-device-type" name="device_type" class="form-select" required>
                            <?php foreach (($deviceTypes ?? []) as $type): ?>
                                <option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" <?= ($product['device_type'] ?? ($deviceTypes[0] ?? '')) === $type ? 'selected' : '' ?>><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if ($mode === 'edit'): ?>
                    <div class="col-md-6">
                        <label class="form-label" for="product-imei">IMEI</label>
                        <input type="text" id="product-imei" name="imei" class="form-control" value="<?= htmlspecialchars($product['imei'] ?? '', ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" autocomplete="off" required>
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <label class="form-label" for="product-imeis">Device IMEIs</label>
                        <textarea id="product-imeis" name="imeis" class="form-control" rows="5" placeholder="One IMEI per line, or separate with commas" inputmode="numeric" autocomplete="off" required><?= htmlspecialchars((string) ($product['imeis'] ?? $product['imei'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="form-text">Every IMEI must be unique. Each IMEI becomes its own device record.</div>
                    </div>
                    <?php endif; ?>
                    <div class="col-md-6">
                        <label class="form-label">Cost Price</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="<?= htmlspecialchars((string) ($product['cost_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Selling Price</label>
                        <input type="number" step="0.01" name="selling_price" class="form-control" value="<?= htmlspecialchars((string) ($product['selling_price'] ?? 0), ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit"><?= $mode === 'edit' ? 'Save Changes' : 'Register Device' ?></button>
                        <a class="btn btn-outline-secondary ms-2" href="/index.php?controller=inventory">Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
