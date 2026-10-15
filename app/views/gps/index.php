<?php $title = 'GPS Devices'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h2 class="h4 mb-1">GPS Devices</h2><p class="text-secondary small mb-0">Operational tracker records, including IMEI, SIM, installation package, and status.</p></div>
    <a class="btn btn-primary" href="/index.php?controller=gps&action=create">New GPS Device</a>
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
                        <th>Serial</th>
                        <th>SIM</th>
                        <th>Status</th>
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
                            <td><?= htmlspecialchars($device['serial'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['sim_number'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['status'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['installed_at'] ?? '—') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" href="/index.php?controller=gps&action=edit&id=<?= (int) ($device['id'] ?? 0) ?>">Edit</a>
                                <form method="post" action="/index.php?controller=gps&action=delete" class="d-inline" onsubmit="return confirm('Delete this device?')">
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
