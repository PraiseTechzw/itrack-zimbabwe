<?php $title = 'GPS Devices'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">GPS Devices</h2>
    <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=gps&action=create">New GPS Device</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Device Name</th>
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
                            <td><?= htmlspecialchars($device['imei'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['serial'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['sim_number'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['status'] ?? '') ?></td>
                            <td><?= htmlspecialchars($device['installed_at'] ?? '—') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" href="/itrack-zimbabwe/public/index.php?controller=gps&action=edit&id=<?= (int) ($device['id'] ?? 0) ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="/itrack-zimbabwe/public/index.php?controller=gps&action=delete&id=<?= (int) ($device['id'] ?? 0) ?>" onclick="return confirm('Delete this device?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
