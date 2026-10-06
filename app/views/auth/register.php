<?php $title = 'Create account'; ?>
<section class="auth-panel" aria-labelledby="auth-title">
    <div class="auth-panel-heading">
        <p class="auth-kicker">GET STARTED</p>
        <h2 id="auth-title">Create your account</h2>
        <p>Set up your staff profile to access the iTrack workspace.</p>
    </div>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <form method="post" class="auth-form">
        <?= $this->csrfField() ?>
        <div class="auth-field">
            <label for="register-name">Full name</label>
            <input id="register-name" class="form-control" name="name" placeholder="Your full name" autocomplete="name" required>
        </div>
        <div class="auth-field">
            <label for="register-email">Email address</label>
            <input id="register-email" class="form-control" type="email" name="email" placeholder="you@company.com" autocomplete="email" required>
        </div>
        <div class="auth-field">
            <label for="register-department">Department</label>
            <input id="register-department" class="form-control" type="text" name="department" placeholder="Department" value="General" autocomplete="organization-title">
        </div>
        <div class="auth-field">
            <label for="register-password">Password</label>
            <input id="register-password" class="form-control" type="password" name="password" placeholder="At least 8 characters" minlength="8" autocomplete="new-password" required>
        </div>
        <button class="btn btn-primary w-100" type="submit">Create account <i class="fa-solid fa-arrow-right"></i></button>
    </form>
    <p class="auth-panel-footer">Already have an account? <a href="/login.php">Sign in</a></p>
    <div class="auth-security-note"><i class="fa-solid fa-shield-halved"></i> Your details are kept secure</div>
</section>
