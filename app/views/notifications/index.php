<?php $title = $title ?? 'Notifications'; ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">Notifications</h2>
        <p class="text-muted mb-0">Manage alerts, reminders, and system messages from one place.</p>
    </div>
    <a class="btn btn-primary" href="/itrack-zimbabwe/public/index.php?controller=notification&action=create">New Notification</a>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Unread</div>
                <div class="value"><?= (int) ($unreadCount ?? 0) ?></div>
                <p class="text-muted mb-0">Messages you have not yet reviewed.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Read</div>
                <div class="value"><?= (int) ($readCount ?? 0) ?></div>
                <p class="text-muted mb-0">Notifications already acknowledged.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stats-card border-0">
            <div class="card-body">
                <div class="title">Total</div>
                <div class="value"><?= (int) ($totalCount ?? 0) ?></div>
                <p class="text-muted mb-0">Total messages in your notification center.</p>
            </div>
        </div>
    </div>
</div>
<div class="card mt-4 border-0">
    <div class="card-body">
        <h5 class="card-title mb-3">Recent notifications</h5>
        <?php if (empty($notifications)): ?>
            <p class="text-muted mb-0">No notifications are available. Use the button above to create one.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $notification): ?>
                            <tr>
                                <td><?= htmlspecialchars($notification['title'] ?? '') ?></td>
                                <td><?= htmlspecialchars(mb_strimwidth($notification['message'] ?? '', 0, 70, '...')) ?></td>
                                <td><?= (int) ($notification['is_read'] ?? 0) === 1 ? 'Read' : 'Unread' ?></td>
                                <td><?= htmlspecialchars($notification['created_at'] ?? '') ?></td>
                                <td>
                                    <a class="btn btn-sm btn-outline-secondary" href="/itrack-zimbabwe/public/index.php?controller=notification&action=edit&id=<?= (int) ($notification['id'] ?? 0) ?>">Edit</a>
                                    <a class="btn btn-sm btn-outline-danger" href="/itrack-zimbabwe/public/index.php?controller=notification&action=delete&id=<?= (int) ($notification['id'] ?? 0) ?>" onclick="return confirm('Delete this notification?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
