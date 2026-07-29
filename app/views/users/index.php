<?php $title = 'User Management'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Users</h2>
    <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=users&action=create">New User</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Department</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['name'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['role'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['department'] ?? '') ?></td>
                        <td><?= htmlspecialchars($user['status'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
