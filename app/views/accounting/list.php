<?php $title = $title ?? 'Accounting'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <p class="text-uppercase text-muted mb-1" style="letter-spacing:.12em; font-size:11px; font-weight:700;">Accounting</p>
        <h2 class="h4 mb-0"><?= htmlspecialchars($title) ?></h2>
    </div>
    <a class="btn btn-primary" href="/index.php?controller=accounting&action=<?= htmlspecialchars($createAction ?? 'index') ?>">New <?= htmlspecialchars($resourceName ?? 'Record') ?></a>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-muted mb-0">No <?= strtolower(htmlspecialchars($resourceName ?? 'records')) ?> found yet.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <?php foreach ($columns as $column): ?>
                                <th><?= htmlspecialchars($column['label'] ?? '') ?></th>
                            <?php endforeach; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <td>
                                        <?php
                                            $value = $item[$column['key']] ?? '';
                                            if (($column['type'] ?? '') === 'money') {
                                                echo '$' . number_format((float) $value, 2);
                                            } else {
                                                echo htmlspecialchars((string) $value);
                                            }
                                        ?>
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <a class="btn btn-sm btn-outline-secondary" href="/index.php?controller=accounting&action=<?= htmlspecialchars($editAction ?? 'index') ?>&id=<?= (int) ($item['id'] ?? 0) ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="/index.php?controller=accounting&action=<?= htmlspecialchars($deleteAction ?? 'index') ?>&id=<?= (int) ($item['id'] ?? 0) ?>" onclick="return confirm('Delete this record?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
