<?php $title = 'User Management'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Users</h2>
    <a class="btn btn-primary" href="/index.php?controller=users&action=create">New User</a>
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
                        <th class="text-end">Actions</th>
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
                        <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="?controller=users&action=edit&id=<?= (int) $user['id'] ?>">Edit</a><?php if ((int) $user['id'] !== (int) ($_SESSION['user']['id'] ?? 0)): ?><a class="btn btn-sm btn-outline-danger" href="?controller=users&action=delete&id=<?= (int) $user['id'] ?>" onclick="return confirm('Delete this user?')">Delete</a><?php endif; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
