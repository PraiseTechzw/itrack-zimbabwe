<?php

/**
 * Regression test for the password-reset screen and flow.
 * Run with: php tests/reset_password_flow.php
 */

// Keep output buffered so the controller can start a session (no headers sent yet).
ob_start();

$database = sys_get_temp_dir() . '/itrack-reset-' . getmypid() . '.sqlite';
@unlink($database);
putenv('DB_DRIVER=sqlite');
putenv('DB_SQLITE_PATH=' . $database);
putenv('APP_ENV=production');
$_ENV['DB_DRIVER'] = 'sqlite';
$_ENV['DB_SQLITE_PATH'] = $database;
$_ENV['APP_ENV'] = 'production';

require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';

$failures = 0;
$results = [];
function checkReset(string $name, bool $condition): void
{
    global $failures, $results;
    if (!$condition) $failures++;
    $results[] = ($condition ? 'PASS  ' : 'FAIL  ') . $name;
}

function renderReset(AuthController $controller, string $method, array $params): string
{
    $_SERVER['REQUEST_METHOD'] = $method;
    $_GET = [];
    $_POST = [];
    if ($method === 'GET') {
        $_GET['token'] = (string) ($params['token'] ?? '');
    } else {
        $_POST = $params;
    }
    ob_start();
    $controller->resetPassword();
    return (string) ob_get_clean();
}

$users = new User();
$email = 'reset-flow@example.com';
$users->create([
    'name' => 'Reset Tester',
    'email' => $email,
    'password' => 'OldPassword123!',
    'role' => 'Staff',
]);
$token = $users->createPasswordResetToken($email);
checkReset('token created', is_string($token) && strlen($token) === 64);

$controller = new AuthController();

$getHtml = renderReset($controller, 'GET', ['token' => $token]);
checkReset('valid link renders the password form', str_contains($getHtml, 'name="password"') && str_contains($getHtml, 'Update password'));
checkReset('valid link does not claim the password was already updated', !str_contains($getHtml, 'Your password has been updated'));
checkReset('form carries the reset token', str_contains($getHtml, 'value="' . $token . '"'));

$badHtml = renderReset($controller, 'GET', ['token' => str_repeat('a', 64)]);
checkReset('unknown token shows an expiry error', str_contains($badHtml, 'invalid or has expired'));
checkReset('unknown token hides the password form', !str_contains($badHtml, 'name="password"'));

preg_match('/name="csrf_token" value="([^"]+)"/', $getHtml, $matches);
$csrf = $matches[1] ?? '';
checkReset('form contains a CSRF token', $csrf !== '');

$newPassword = 'BrandNewPassword123!';
$postHtml = renderReset($controller, 'POST', [
    'token' => $token,
    'csrf_token' => $csrf,
    'password' => $newPassword,
    'password_confirmation' => $newPassword,
]);
checkReset('successful reset shows a confirmation', str_contains($postHtml, 'Your password has been updated'));
checkReset('new password authenticates', $users->authenticate($email, $newPassword) !== null);
checkReset('old password no longer works', $users->authenticate($email, 'OldPassword123!') === null);
checkReset('token is single use', $users->isValidPasswordResetToken($token) === false);

// A POST on a session that never rendered a form has no CSRF token in the
// session either; empty-vs-empty must not compare equal and reset the password.
$secondEmail = 'reset-csrf@example.com';
$users->create([
    'name' => 'CSRF Tester',
    'email' => $secondEmail,
    'password' => 'OldPassword123!',
    'role' => 'Staff',
]);
$secondToken = $users->createPasswordResetToken($secondEmail);
unset($_SESSION['csrf_token']);
$forgedHtml = renderReset($controller, 'POST', [
    'token' => $secondToken,
    'password' => $newPassword,
    'password_confirmation' => $newPassword,
]);
checkReset('missing CSRF token is rejected', str_contains($forgedHtml, 'Your session expired'));
checkReset('password unchanged after rejected CSRF', $users->authenticate($secondEmail, 'OldPassword123!') !== null);

@unlink($database);
ob_end_clean();
foreach ($results as $line) echo $line, "\n";
echo $failures === 0 ? "\nAll reset-flow checks passed.\n" : "\n{$failures} reset-flow check(s) failed.\n";
exit($failures === 0 ? 0 : 1);
