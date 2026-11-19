<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/helpers/mail.php';
require_once dirname(__DIR__) . '/helpers/settings.php';
require_once dirname(__DIR__) . '/services/EmailTemplate.php';

class AuthController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function login(): void
    {
        $this->ensureSession();
        $config = require dirname(__DIR__) . '/config/app.php';

        if (!empty($_SESSION['user'])) {
            $this->redirect('/dashboard.php');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            if (!$this->validateCsrf()) {
                $error = 'Invalid security token.';
            } else {
                $maxAttempts = max(3, setting_int('max_login_attempts', 5));
                if (($_SESSION['login_attempts'] ?? 0) >= $maxAttempts) {
                    $error = 'Too many failed attempts. Please contact an administrator.';
                } else {
                    $user = $this->userModel->authenticate($email, $password);
                    if ($user) {
                        unset($_SESSION['login_attempts']);
                        session_regenerate_id(true);
                        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        $_SESSION['user'] = $user;
                        $_SESSION['user']['logged_in_at'] = date('Y-m-d H:i:s');
                        $this->redirect('/dashboard.php');
                    }
                    $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
                    $error = 'Invalid credentials';
                }
            }
        }

        $notice = trim((string) ($_SESSION['registration_success'] ?? ''));
        unset($_SESSION['registration_success']);
        $this->view('auth/login', [
            'title' => 'Login',
            'error' => $error,
            'notice' => $notice,
            'registrationAllowed' => setting_bool('allow_registration', !empty($config['allow_registration'])),
        ]);
    }

    public function logout(): void
    {
        $this->ensureSession();
        session_unset();
        session_destroy();
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', [
                'expires' => time() - 42000,
                'path' => $params['path'],
                'domain' => $params['domain'],
                'secure' => $params['secure'],
                'httponly' => $params['httponly'],
                'samesite' => $params['samesite'] ?? 'Lax',
            ]);
        }
        $this->redirect('/login.php');
    }

    public function register(): void
    {
        $this->ensureSession();
        if (!empty($_SESSION['user'])) {
            $this->redirect('/dashboard.php');
        }
        $config = require dirname(__DIR__) . '/config/app.php';
        if (!setting_bool('allow_registration', !empty($config['allow_registration']))) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Page not found']);
            return;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $this->sanitize($_POST['name'] ?? '');
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $department = $this->sanitize($_POST['department'] ?? 'General');
            $password = (string) ($_POST['password'] ?? '');
            $confirmation = (string) ($_POST['password_confirmation'] ?? '');
            $minimum = setting_int('password_min_length', 12);
            if (!$this->validateCsrf()) {
                $error = 'Invalid security token.';
            } elseif (strlen($name) < 2 || strlen($name) > 150) {
                $error = 'Enter your full name (2 to 150 characters).';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Enter a valid email address.';
            } elseif (strlen($password) < $minimum) {
                $error = 'Password must be at least ' . $minimum . ' characters.';
            } elseif (!hash_equals($password, $confirmation)) {
                $error = 'The passwords do not match.';
            } elseif ($this->userModel->findByEmail($email)) {
                $error = 'An account with that email already exists.';
            } else {
                $this->userModel->create([
                    'name' => $name,
                    'email' => $email,
                    'password' => $password,
                    'role' => 'Staff',
                    'department' => $department !== '' ? $department : 'General',
                    'status' => 'active',
                ]);
                $_SESSION['registration_success'] = 'Your staff account is ready. Sign in to start working.';
                $this->redirect('/login.php');
            }
        }
        $this->view('auth/register', ['title' => 'Register', 'error' => $error]);
    }

    public function forgotPassword(): void
    {
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validateCsrf()) {
                $email = strtolower(trim((string) ($_POST['email'] ?? '')));
                $config = require dirname(__DIR__) . '/config/app.php';
                $resetBaseUrl = setting_string('public_url', (string) ($config['public_url'] ?? ''));
                $isSecureUrl = str_starts_with($resetBaseUrl, 'https://')
                    || ($config['environment'] !== 'production' && str_starts_with($resetBaseUrl, 'http://'));
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && $isSecureUrl) {
                    $token = $this->userModel->createPasswordResetToken($email);
                    if ($token !== null) {
                        $resetLink = $resetBaseUrl . '/index.php?controller=auth&action=resetPassword&token=' . urlencode($token);
                        $template = EmailTemplate::passwordReset(
                            setting_string('app_name', (string) ($config['app_name'] ?? 'iTrack Zimbabwe')),
                            $resetLink
                        );
                        $sent = sendMail($email, $template['subject'], $template['text'], $template['html']);
                        if (!$sent) $this->userModel->removePasswordResetTokensForEmail($email);
                    }
                } else {
                    error_log('Password reset unavailable: configure APP_PUBLIC_URL and MAIL_FROM.');
                }
                $message = 'If an active account matches that address, reset instructions will be sent shortly.';
            } else {
                $message = 'Invalid security token.';
            }
        }

        $this->view('auth/forgot-password', ['title' => 'Forgot Password', 'message' => $message]);
    }

    public function resetPassword(): void
    {
        $this->ensureSession();
        $token = trim((string) ($_POST['token'] ?? $_GET['token'] ?? ''));
        $success = false;
        $error = '';
        $showForm = false;
        $minLength = setting_int('password_min_length', 12);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $error = 'Your session expired. Reload the link and try again.';
                $showForm = true;
            } elseif (!$this->userModel->isValidPasswordResetToken($token)) {
                $error = 'This reset link is invalid or has expired. Request a new one.';
            } elseif (strlen((string) ($_POST['password'] ?? '')) < $minLength) {
                $error = 'Choose a password with at least ' . $minLength . ' characters.';
                $showForm = true;
            } elseif (!hash_equals((string) ($_POST['password'] ?? ''), (string) ($_POST['password_confirmation'] ?? ''))) {
                $error = 'The passwords do not match.';
                $showForm = true;
            } else {
                $success = $this->userModel->resetPassword($token, (string) $_POST['password']);
                if ($success) {
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                } else {
                    $error = 'This reset link is invalid or has expired. Request a new one.';
                }
            }
        } elseif ($this->userModel->isValidPasswordResetToken($token)) {
            // Opening a valid link must show the form, never a success message.
            $showForm = true;
        } else {
            $error = 'This reset link is invalid or has expired. Request a new one.';
        }

        $this->view('auth/reset-password', [
            'title' => 'Choose a new password',
            'token' => $token,
            'success' => $success,
            'error' => $error,
            'showForm' => $showForm,
            'minLength' => $minLength,
        ]);
    }

    public function changePassword(): void
    {
        $this->requireLogin();
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validateCsrf()) {
                $password = (string) ($_POST['password'] ?? '');
                $userId = (int) ($_SESSION['user']['id'] ?? 0);
                $currentPassword = (string) ($_POST['current_password'] ?? '');
                if (!$this->userModel->verifyPassword($userId, $currentPassword)) {
                    $message = 'Current password is incorrect.';
                } elseif (strlen($password) < setting_int('password_min_length', 12)) {
                    $message = 'Choose a password with at least ' . setting_int('password_min_length', 12) . ' characters.';
                } elseif (!hash_equals($password, (string) ($_POST['password_confirmation'] ?? ''))) {
                    $message = 'The passwords do not match.';
                } else {
                    $this->userModel->updatePassword($userId, $password);
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $message = 'Password updated successfully.';
                }
            } else {
                $message = 'Invalid security token.';
            }
        }

        $this->view('auth/change-password', ['title' => 'Change Password', 'message' => $message]);
    }
}
