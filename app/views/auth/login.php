<?php $title = 'Login'; ?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h4 text-center mb-3">Sign in to iTrack Zimbabwe</h2>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <form method="post">
                    <?= $this->csrfField() ?>
                    <div class="mb-3">
                        <label class="form-label">Email address</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Sign In</button>
                </form>
                <div class="mt-3 text-center">
                    <a href="/itrack-zimbabwe/public/index.php?controller=auth&action=forgotPassword">Forgot password?</a>
                </div>
            </div>
        </div>
    </div>
</div>
