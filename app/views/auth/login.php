<?php $title = 'Login'; ?>
<?= consoleLog('Login page loaded', ['method' => $_SERVER['REQUEST_METHOD'] ?? 'GET', 'error' => $error]) ?>
<section class="auth-panel" aria-labelledby="auth-title">
    <div class="auth-panel-heading">
        <p class="auth-kicker">ACCOUNT ACCESS</p>
        <h2 id="auth-title">Welcome back</h2>
        <p>Sign in to continue to your iTrack workspace.</p>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?= consoleLog('Login error shown', ['error' => $error]) ?>
    <?php endif; ?>
    <form method="post" class="auth-form">
        <?= $this->csrfField() ?>
        <div class="auth-field">
            <label for="login-email">Email address</label>
            <input id="login-email" type="email" name="email" class="form-control" placeholder="you@company.com" autocomplete="email" required>
        </div>
        <div class="auth-field">
            <label for="login-password">Password</label>
            <input id="login-password" type="password" name="password" class="form-control" placeholder="Enter your password" autocomplete="current-password" required>
        </div>
        <div class="auth-form-meta">
            <span>Secure sign in</span>
            <a href="/index.php?controller=auth&action=forgotPassword">Forgot password?</a>
        </div>
        <button class="btn btn-primary w-100" type="submit">Sign in <i class="fa-solid fa-arrow-right"></i></button>
    </form>
    <p class="auth-panel-footer">Need access to iTrack? <a href="/index.php?controller=auth&action=register">Create a staff account</a></p>
    <div class="auth-security-note"><i class="fa-solid fa-shield-halved"></i> Your account is protected with secure access</div>
</section>
