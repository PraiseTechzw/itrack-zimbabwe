<?php $mode = $mode ?? 'create'; $product = $product ?? []; $title = $mode === 'edit' ? 'Edit Product' : 'Create Product'; ?>
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit Product' : 'Create Product' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <p class="text-secondary small mb-4"><?= $mode === 'edit' ? 'Each physical device must have its own unique 15-digit IMEI.' : 'Enter a 15-digit IMEI for each device. Each IMEI creates a separate inventory record.' ?></p>
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
                    <?php if ($mode === 'edit'): ?><div class="col-md-6"><label class="form-label" for="product-sim-number">SIM Number</label><input type="text" id="product-sim-number" name="sim_number" class="form-control" value="<?= htmlspecialchars($product['sim_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>"></div><?php endif; ?>
                    <?php if ($mode === 'edit'): ?>
                    <div class="col-md-6">
                        <label class="form-label" for="product-imei">IMEI</label>
                        <input type="text" id="product-imei" name="imei" class="form-control" value="<?= htmlspecialchars($product['imei'] ?? '', ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" pattern="[0-9]{15}" maxlength="15" autocomplete="off" required>
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <label class="form-label">Device IMEIs</label>
                        <div id="inventory-imeis" class="vstack gap-2">
                            <?php $imeiRows = is_array($product['imeis'] ?? null) ? $product['imeis'] : (empty($product['imei']) ? [''] : [$product['imei']]); foreach ($imeiRows as $imei): ?>
                                <div class="input-group imei-row"><input type="text" name="imeis[]" class="form-control" value="<?= htmlspecialchars((string) $imei, ENT_QUOTES, 'UTF-8') ?>" placeholder="15-digit IMEI" inputmode="numeric" pattern="[0-9]{15}" maxlength="15" autocomplete="off" required><button class="btn btn-outline-danger remove-imei" type="button" aria-label="Remove IMEI">Remove</button></div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" id="add-inventory-imei" type="button">+ Add another device</button>
                        <div class="form-text">Enter exactly 15 digits for each unique IMEI.</div>
                    </div>
                    <?php endif; ?>
                    <?php if ($mode !== 'edit'): ?><div class="col-12">
                        <label class="form-label">SIM Numbers</label>
                        <div id="inventory-sims" class="vstack gap-2">
                            <?php $simRows = is_array($product['sim_numbers'] ?? null) ? $product['sim_numbers'] : ['']; foreach ($simRows as $sim): ?>
                                <div class="input-group sim-row"><input type="text" name="sim_numbers[]" class="form-control" value="<?= htmlspecialchars((string) $sim, ENT_QUOTES, 'UTF-8') ?>" placeholder="SIM number"><button class="btn btn-outline-danger remove-sim" type="button">Remove</button></div>
                            <?php endforeach; ?>
                        </div>
                        <button class="btn btn-sm btn-outline-primary mt-2" id="add-inventory-sim" type="button">+ Add another SIM number</button>
                        <div class="form-text">SIM numbers are assigned to device records in the order entered.</div>
                    </div><?php endif; ?>
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
<?php if ($mode !== 'edit'): ?><script>
document.getElementById('add-inventory-imei').addEventListener('click', () => {
    const row = document.querySelector('#inventory-imeis .imei-row').cloneNode(true);
    row.querySelector('input').value = '';
    document.getElementById('inventory-imeis').append(row);
});
document.getElementById('inventory-imeis').addEventListener('click', event => {
    if (event.target.closest('.remove-imei') && document.querySelectorAll('#inventory-imeis .imei-row').length > 1) event.target.closest('.imei-row').remove();
});
document.getElementById('add-inventory-sim').addEventListener('click', () => {
    const row = document.querySelector('#inventory-sims .sim-row').cloneNode(true);
    row.querySelector('input').value = '';
    document.getElementById('inventory-sims').append(row);
});
document.getElementById('inventory-sims').addEventListener('click', event => {
    if (event.target.closest('.remove-sim') && document.querySelectorAll('#inventory-sims .sim-row').length > 1) event.target.closest('.sim-row').remove();
});
</script><?php endif; ?>
