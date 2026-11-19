<?php $title = 'Choose a new password'; ?>
<?php $minLength = (int) ($minLength ?? 12); ?>
<section class="auth-panel" aria-labelledby="auth-title">
    <div class="auth-panel-heading">
        <p class="auth-kicker">ACCOUNT RECOVERY</p>
        <h2 id="auth-title">Choose a new password</h2>
        <p>Create a strong password for your iTrack account. Use at least <?= $minLength ?> characters — reset links expire after one hour.</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2" aria-hidden="true"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success" role="status">
            <i class="fa-solid fa-circle-check me-2" aria-hidden="true"></i>Your password has been updated. You can now sign in with your new password.
        </div>
        <a class="btn btn-primary w-100" href="/login.php">Back to sign in <i class="fa-solid fa-arrow-right"></i></a>
    <?php elseif (!empty($showForm)): ?>
        <form method="post" class="auth-form">
            <?= $this->csrfField() ?>
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <div class="auth-field">
                <label for="reset-password">New password</label>
                <div class="auth-password-field">
                    <input id="reset-password" type="password" name="password" class="form-control" minlength="<?= $minLength ?>" autocomplete="new-password" placeholder="At least <?= $minLength ?> characters" required autofocus>
                    <button class="auth-password-toggle" type="button" data-toggle-target="reset-password" aria-controls="reset-password" aria-label="Show or hide new password" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                </div>
                <span class="auth-password-hint">Use a mix of letters, numbers and symbols.</span>
            </div>
            <div class="auth-field">
                <label for="reset-password-confirmation">Confirm new password</label>
                <div class="auth-password-field">
                    <input id="reset-password-confirmation" type="password" name="password_confirmation" class="form-control" minlength="<?= $minLength ?>" autocomplete="new-password" placeholder="Re-enter your new password" required>
                    <button class="auth-password-toggle" type="button" data-toggle-target="reset-password-confirmation" aria-controls="reset-password-confirmation" aria-label="Show or hide password confirmation" aria-pressed="false"><i class="fa-solid fa-eye" aria-hidden="true"></i></button>
                </div>
            </div>
            <button class="btn btn-primary w-100" type="submit">Update password <i class="fa-solid fa-arrow-right"></i></button>
        </form>
        <script>
            (function () {
                document.querySelectorAll('[data-toggle-target]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        var input = document.getElementById(button.getAttribute('data-toggle-target'));
                        if (!input) return;
                        var show = input.type === 'password';
                        input.type = show ? 'text' : 'password';
                        button.setAttribute('aria-pressed', show ? 'true' : 'false');
                        var icon = button.querySelector('i');
                        if (icon) icon.className = show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
                    });
                });
            })();
        </script>
    <?php endif; ?>

    <?php if (empty($success)): ?>
        <p class="auth-panel-footer">Link expired or never arrived? <a href="/index.php?controller=auth&amp;action=forgotPassword">Request another reset link</a></p>
        <div class="auth-security-note"><i class="fa-solid fa-shield-halved"></i> Your new password is stored securely</div>
    <?php endif; ?>
</section>
