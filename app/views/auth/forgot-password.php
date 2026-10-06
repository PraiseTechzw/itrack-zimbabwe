<?php $title = 'Forgot Password'; ?>
<section class="auth-panel" aria-labelledby="auth-title">
    <div class="auth-panel-heading">
        <p class="auth-kicker">ACCOUNT RECOVERY</p>
        <h2 id="auth-title">Reset your password</h2>
        <p>Enter the email address associated with your account.</p>
    </div>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info" role="status"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" class="auth-form">
        <?= $this->csrfField() ?>
        <div class="auth-field">
            <label for="reset-email">Email address</label>
            <input id="reset-email" type="email" name="email" class="form-control" placeholder="you@company.com" autocomplete="email" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Send reset request <i class="fa-solid fa-arrow-right"></i></button>
    </form>
    <p class="auth-panel-footer"><a href="/login.php"><i class="fa-solid fa-arrow-left me-1"></i> Back to sign in</a></p>
    <div class="auth-security-note"><i class="fa-solid fa-shield-halved"></i> We’ll help you get back into your account</div>
</section>
