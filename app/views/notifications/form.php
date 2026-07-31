<?php $title = $mode === 'edit' ? 'Edit Notification' : 'Create Notification'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit Notification' : 'Create Notification' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($notification['title'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Target User ID</label>
                        <input type="number" name="user_id" class="form-control" value="<?= htmlspecialchars((string) ($notification['user_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-text">Leave blank to target all users.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required><?= htmlspecialchars($notification['message'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="is_read" class="form-select">
                            <option value="0" <?= isset($notification['is_read']) && (int) $notification['is_read'] === 0 ? 'selected' : '' ?>>Unread</option>
                            <option value="1" <?= isset($notification['is_read']) && (int) $notification['is_read'] === 1 ? 'selected' : '' ?>>Read</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save Notification</button>
                        <a class="btn btn-outline-secondary ms-2" href="/itrack-zimbabwe/public/index.php?controller=notification">Back to notifications</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
