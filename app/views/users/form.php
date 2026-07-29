<?php $title = 'Create User'; ?>
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h2 class="h4 mb-4">Create User</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post" class="row g-3">
                    <?= $this->csrfField() ?>
                    <div class="col-md-6">
                        <label class="form-label">Full name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select">
                            <option>Staff</option>
                            <option>Administrator</option>
                            <option>Director</option>
                            <option>Finance Officer</option>
                            <option>Procurement Officer</option>
                            <option>Store Officer</option>
                            <option>Sales Officer</option>
                            <option>Technician</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <input type="text" name="department" class="form-control" value="General">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary" type="submit">Save User</button>
                        <a class="btn btn-outline-secondary ms-2" href="/itrack-zimbabwe/public/index.php?controller=users">Back to list</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
