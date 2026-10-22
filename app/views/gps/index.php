<?php $title = 'Installations'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="h4 mb-1">Installations</h2><p class="text-secondary small mb-0">Record installations using equipment available in inventory.</p></div>
    <div class="d-flex gap-2"><a class="btn btn-outline-primary" href="/index.php?controller=gps&action=createBulk"><i class="fa-solid fa-layer-group me-2"></i>Bulk install</a><a class="btn btn-primary" href="/index.php?controller=gps&action=create">New Installation</a></div>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Device Type</th>
                        <th>Package</th>
                        <th>IMEI</th>
                        <th>SIM</th>
                        <th>Installed At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= htmlspecialchars($device['device_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['device_type'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($device['package_type'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($device['imei'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['sim_number'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['installed_at'] ?? '—') ?></td>
                            <td>
                                <form method="post" action="/index.php?controller=gps&action=delete" class="d-inline" data-confirm="Delete this device?">
                                    <?= $this->csrfField() ?>
                                    <input type="hidden" name="id" value="<?= (int) ($device['id'] ?? 0) ?>">
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
