<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';

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
                $user = $this->userModel->authenticate($email, $password);
                if ($user) {
                    session_regenerate_id(true);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                    $_SESSION['user'] = $user;
                    $_SESSION['user']['logged_in_at'] = date('Y-m-d H:i:s');
                    $userId = (int) ($user['id'] ?? 0);
                    if ($userId > 0) {
                        createNotification($userId, 'Welcome back', 'You have successfully signed in to the iTrack dashboard.');
                    }
                    $this->redirect('/dashboard.php');
                }

                $error = 'Invalid credentials';
            }
        }

        $this->view('auth/login', ['title' => 'Login', 'error' => $error]);
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
        if (empty($config['allow_registration'])) {
            http_response_code(404);
            $this->view('errors/404', ['title' => 'Page not found']);
            return;
        }
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $error = 'Invalid security token.';
            } elseif (strlen((string) ($_POST['password'] ?? '')) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($this->userModel->findByEmail($this->sanitize($_POST['email'] ?? ''))) {
                $error = 'An account with that email already exists.';
            } else {
                $this->userModel->create([
                    'name' => $this->sanitize($_POST['name'] ?? ''),
                    'email' => $this->sanitize($_POST['email'] ?? ''),
                    'password' => (string) ($_POST['password'] ?? ''),
                    'role' => 'Staff',
                    'department' => $this->sanitize($_POST['department'] ?? 'General'),
                    'status' => 'active',
                ]);
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
                $email = $this->sanitize($_POST['email'] ?? '');
                $user = $this->userModel->findByEmail($email);
                if ($user) {
                    $message = 'Password reset instructions were prepared for ' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '.';
                } else {
                    $message = 'No account found for that email address.';
                }
            } else {
                $message = 'Invalid security token.';
            }
        }

        $this->view('auth/forgot-password', ['title' => 'Forgot Password', 'message' => $message]);
    }

    public function changePassword(): void
    {
        $this->requireLogin();
        $message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->validateCsrf()) {
                $password = (string) ($_POST['password'] ?? '');
                $this->userModel->updatePassword((int) ($_SESSION['user']['id'] ?? 0), $password);
                $message = 'Password updated successfully.';
            } else {
                $message = 'Invalid security token.';
            }
        }

        $this->view('auth/change-password', ['title' => 'Change Password', 'message' => $message]);
    }
}
