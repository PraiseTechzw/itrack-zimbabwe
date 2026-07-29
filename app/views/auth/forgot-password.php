<?php $title = 'Forgot Password'; ?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h5 mb-3">Reset password</h2>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-info" role="alert"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post">
                    <?= $this->csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <button class="btn btn-primary" type="submit">Send Reset Request</button>
                    <a class="btn btn-outline-secondary ms-2" href="/itrack-zimbabwe/public/login.php">Back to login</a>
                </form>
            </div>
        </div>
    </div>
</div>
