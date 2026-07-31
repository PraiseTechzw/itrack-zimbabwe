<?php $title = 'Suppliers'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Suppliers</h2>
    <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=supplier&action=create">New Supplier</a>
</div>
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?= htmlspecialchars($supplier['company_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($supplier['contact_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($supplier['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($supplier['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($supplier['status'] ?? '') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" href="/itrack-zimbabwe/public/index.php?controller=supplier&action=edit&id=<?= (int) ($supplier['id'] ?? 0) ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="/itrack-zimbabwe/public/index.php?controller=supplier&action=delete&id=<?= (int) ($supplier['id'] ?? 0) ?>" onclick="return confirm('Delete this supplier?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
