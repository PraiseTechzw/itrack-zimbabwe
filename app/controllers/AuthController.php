<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/helpers/logger.php';
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
            $this->redirect('/itrack-zimbabwe/public/dashboard.php');
        }

        $error = '';
        debugLog('AuthController::login start', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'GET',
            'session_user_exists' => !empty($_SESSION['user']),
        ]);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->sanitize($_POST['email'] ?? '');
            $password = (string) ($_POST['password'] ?? '');

            debugLog('AuthController::login form submitted', [
                'email' => $email,
                'csrf_token' => $_POST['csrf_token'] ?? null,
            ]);

            if (!$this->validateCsrf()) {
                debugLog('AuthController::login invalid CSRF', [
                    'session_csrf' => $_SESSION['csrf_token'] ?? null,
                    'posted_csrf' => $_POST['csrf_token'] ?? null,
                ]);
                $error = 'Invalid security token.';
            } else {
                $user = $this->userModel->authenticate($email, $password);
                if ($user) {
                    debugLog('AuthController::login success', [
                        'email' => $email,
                        'user_id' => $user['id'] ?? null,
                    ]);
                    $_SESSION['user'] = $user;
                    $_SESSION['user']['logged_in_at'] = date('Y-m-d H:i:s');
                    $this->redirect('/itrack-zimbabwe/public/dashboard.php');
                }

                debugLog('AuthController::login invalid credentials', [
                    'email' => $email,
                ]);
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
        $this->redirect('/itrack-zimbabwe/public/login.php');
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
