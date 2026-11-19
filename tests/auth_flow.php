<?php

/**
 * Regression checks for authentication primitives.
 * Run with: php tests/auth_flow.php
 */

$database = sys_get_temp_dir() . '/itrack-auth-' . getmypid() . '.sqlite';
@unlink($database);
putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=' . $database);
putenv('APP_ENV=production');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_SQLITE_PATH'] = $database;
$_ENV['APP_ENV'] = 'production';

require_once dirname(__DIR__) . '/app/models/User.php';

$failures = 0;
function checkAuth(string $name, bool $condition): void
{
    global $failures;
    if ($condition) {
        echo "PASS  {$name}\n";
        return;
    }
    $failures++;
    echo "FAIL  {$name}\n";
}

$users = new User();
$email = 'Case.User@Example.com';
$password = 'StrongPassword123!';
$id = $users->create([
    'name' => 'Case User',
    'email' => $email,
    'password' => $password,
    'role' => 'Staff',
]);

checkAuth('new accounts store lower-cased emails', $users->find((int) $id)['email'] === strtolower($email));

foreach ([$email, strtoupper($email), ucfirst($email)] as $variant) {
    checkAuth('findByEmail matches ' . $variant, $users->findByEmail($variant) !== null);
    $authenticated = $users->authenticate($variant, $password);
    checkAuth('authenticate matches ' . $variant, $authenticated !== null && (int) $authenticated['id'] === (int) $id);
    checkAuth('authenticate never exposes the password hash ' . $variant, !isset($authenticated['password_hash']));
}

checkAuth('wrong password is rejected', $users->authenticate($email, 'wrong-password') === null);
checkAuth('duplicate email with different case is detected', $users->findByEmail(strtoupper($email)) !== null);

// Updates must preserve the same normalisation.
$users->update((int) $id, [
    'name' => 'Case User',
    'email' => 'UPDATED.User@Example.com',
    'role' => 'Staff',
    'department' => 'IT',
    'status' => 'active',
]);
checkAuth('updates store lower-cased emails', $users->find((int) $id)['email'] === 'updated.user@example.com');
checkAuth('login works after an email update', $users->authenticate('UPDATED.USER@EXAMPLE.COM', $password) !== null);

@unlink($database);
echo $failures === 0 ? "\nAll auth-flow checks passed.\n" : "\n{$failures} auth-flow check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
