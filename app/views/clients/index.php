<?php $title = 'Clients'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="h4 mb-0">Clients</h2>
    <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=client&action=create">New Client</a>
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
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><?= htmlspecialchars($client['company_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['contact_name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['email'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($client['status'] ?? '') ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-secondary" href="/itrack-zimbabwe/public/index.php?controller=client&action=edit&id=<?= (int) ($client['id'] ?? 0) ?>">Edit</a>
                                <a class="btn btn-sm btn-outline-danger" href="/itrack-zimbabwe/public/index.php?controller=client&action=delete&id=<?= (int) ($client['id'] ?? 0) ?>" onclick="return confirm('Delete this client?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
