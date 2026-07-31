<?php $title = $title ?? ($mode === 'edit' ? 'Edit GPS Device' : 'Create GPS Device'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit GPS Device' : 'Create GPS Device' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label">Device Name</label>
                        <input type="text" name="device_name" class="form-control" value="<?= htmlspecialchars($device['device_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">IMEI</label>
                        <input type="text" name="imei" class="form-control" value="<?= htmlspecialchars($device['imei'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Serial</label>
                        <input type="text" name="serial" class="form-control" value="<?= htmlspecialchars($device['serial'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">SIM Number</label>
                        <input type="text" name="sim_number" class="form-control" value="<?= htmlspecialchars($device['sim_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="available" <?= isset($device['status']) && $device['status'] === 'available' ? 'selected' : '' ?>>Available</option>
                            <option value="installed" <?= isset($device['status']) && $device['status'] === 'installed' ? 'selected' : '' ?>>Installed</option>
                            <option value="inactive" <?= isset($device['status']) && $device['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Installed At</label>
                        <input type="datetime-local" name="installed_at" class="form-control" value="<?= htmlspecialchars(!empty($device['installed_at']) ? str_replace(' ', 'T', $device['installed_at']) : '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save Device</button>
                        <a class="btn btn-outline-secondary ms-2" href="/itrack-zimbabwe/public/index.php?controller=gps">Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
