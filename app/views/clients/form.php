<?php $title = $title ?? ($mode === 'edit' ? 'Edit Client' : 'Create Client'); ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4"><?= $mode === 'edit' ? 'Edit Client' : 'Create Client' ?></h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label">Company Name</label>
                        <input type="text" name="company_name" class="form-control" value="<?= htmlspecialchars($client['company_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Contact Name</label>
                        <input type="text" name="contact_name" class="form-control" value="<?= htmlspecialchars($client['contact_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($client['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Phone</label>
                        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($client['phone'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Address</label>
                        <textarea name="address" class="form-control" rows="4"><?= htmlspecialchars($client['address'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active" <?= isset($client['status']) && $client['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= isset($client['status']) && $client['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save Client</button>
                        <a class="btn btn-outline-secondary ms-2" href="/itrack-zimbabwe/public/index.php?controller=client">Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
