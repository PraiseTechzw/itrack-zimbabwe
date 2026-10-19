<?php $title = 'Choose a new password'; ?>
<section class="auth-panel" aria-labelledby="auth-title">
    <div class="auth-panel-heading">
        <p class="auth-kicker">ACCOUNT RECOVERY</p>
        <h2 id="auth-title">Choose a new password</h2>
        <p>Use at least 12 characters. Reset links expire after one hour.</p>
    </div>
    <?php if (!empty($error)): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status">Your password has been updated.</div>
        <a class="btn btn-primary w-100" href="/login.php">Back to sign in</a>
    <?php elseif (empty($error) || !empty($token)): ?>
        <form method="post" class="auth-form">
            <?= $this->csrfField() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="auth-field"><label for="reset-password">New password</label><input id="reset-password" type="password" name="password" class="form-control" minlength="12" autocomplete="new-password" required></div>
            <div class="auth-field"><label for="reset-password-confirmation">Confirm new password</label><input id="reset-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="12" autocomplete="new-password" required></div>
            <button class="btn btn-primary w-100" type="submit">Update password</button>
        </form>
    <?php endif; ?>
    <?php if (empty($success)): ?><p class="auth-panel-footer"><a href="/index.php?controller=auth&amp;action=forgotPassword">Request another reset link</a></p><?php endif; ?>
</section>
