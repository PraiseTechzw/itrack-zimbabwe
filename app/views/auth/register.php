<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h4 text-center mb-3">Create a staff account</h2>
                <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
                <form method="post" class="vstack gap-3">
                    <?= $this->csrfField() ?>
                    <input class="form-control" name="name" placeholder="Full name" required>
                    <input class="form-control" type="email" name="email" placeholder="Email address" required>
                    <input class="form-control" type="text" name="department" placeholder="Department" value="General">
                    <input class="form-control" type="password" name="password" placeholder="Password (8+ characters)" minlength="8" required>
                    <button class="btn btn-primary" type="submit">Create account</button>
                </form>
                <p class="text-center mt-3 mb-0"><a href="/login.php">Back to sign in</a></p>
            </div>
        </div>
    </div>
</div>
