<?php $title = $title ?? ($mode === 'edit' ? 'Edit GPS Device' : 'Create GPS Device'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit GPS Device' : 'Create GPS Device' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <?php if ($mode === 'create'): ?><p class="text-secondary small mb-4">Enter one or more unique IMEIs. Each IMEI creates a separate GPS device record.</p><?php endif; ?>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label"><?= $mode === 'edit' ? 'Device Name' : 'Batch / Device Name' ?></label>
                        <input type="text" name="device_name" class="form-control mb-2" value="<?= htmlspecialchars($device['device_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="Device name" required>
                        <select name="device_type" class="form-select" aria-label="GPS device type">
                            <?php foreach (($deviceTypes ?? []) as $type): ?><option value="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>" <?= ($device['device_type'] ?? 'Galileosky 7x') === $type ? 'selected' : '' ?>><?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Package Type</label>
                        <select name="package_type" class="form-select">
                            <?php foreach (($packageTypes ?? []) as $package): ?><option value="<?= htmlspecialchars($package, ENT_QUOTES, 'UTF-8') ?>" <?= ($device['package_type'] ?? 'Basic installation') === $package ? 'selected' : '' ?>><?= htmlspecialchars($package, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SIM Number</label>
                        <input type="text" name="sim_number" class="form-control" value="<?= htmlspecialchars($device['sim_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <?php if ($mode === 'edit'): ?>
                        <div class="col-md-6">
                            <label class="form-label">IMEI</label>
                        <input type="text" name="imei" class="form-control" value="<?= htmlspecialchars($device['imei'] ?? '', ENT_QUOTES, 'UTF-8') ?>" inputmode="numeric" pattern="[0-9]{15}" maxlength="15" required>
                        </div>
                    <?php else: ?>
                        <div class="col-12">
                            <label class="form-label">Device IMEIs</label>
                            <div id="gps-imeis" class="vstack gap-2">
                                <?php $imeiRows = is_array($device['imeis'] ?? null) ? $device['imeis'] : (empty($device['imei']) ? [''] : [$device['imei']]); foreach ($imeiRows as $imei): ?>
                                    <div class="input-group imei-row"><input type="text" name="imeis[]" class="form-control" value="<?= htmlspecialchars((string) $imei, ENT_QUOTES, 'UTF-8') ?>" placeholder="15-digit IMEI" inputmode="numeric" pattern="[0-9]{15}" maxlength="15" autocomplete="off" required><button class="btn btn-outline-danger remove-imei" type="button" aria-label="Remove IMEI">Remove</button></div>
                                <?php endforeach; ?>
                            </div>
                            <button class="btn btn-sm btn-outline-primary mt-2" id="add-gps-imei" type="button">+ Add another device</button>
                            <div class="form-text">Enter exactly 15 digits for each unique IMEI.</div>
                        </div>
                    <?php endif; ?>
                    <?php if ($mode === 'edit'): ?>
                        <div class="col-md-6">
                            <label class="form-label">Installed At</label>
                            <input type="datetime-local" name="installed_at" class="form-control" value="<?= htmlspecialchars(!empty($device['installed_at']) ? str_replace(' ', 'T', $device['installed_at']) : '', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save Device</button>
                        <a class="btn btn-outline-secondary ms-2" href="/index.php?controller=gps">Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php if ($mode !== 'edit'): ?><script>
document.getElementById('add-gps-imei').addEventListener('click', () => {
    const row = document.querySelector('#gps-imeis .imei-row').cloneNode(true);
    row.querySelector('input').value = '';
    document.getElementById('gps-imeis').append(row);
});
document.getElementById('gps-imeis').addEventListener('click', event => {
    if (event.target.closest('.remove-imei') && document.querySelectorAll('#gps-imeis .imei-row').length > 1) event.target.closest('.imei-row').remove();
});
</script><?php endif; ?>
