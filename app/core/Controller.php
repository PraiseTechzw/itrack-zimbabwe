<?php

require_once dirname(__DIR__) . '/helpers/auth.php';

class Controller
{
    protected function view(string $viewPath, array $data = []): void
    {
        $file = dirname(__DIR__) . '/views/' . $viewPath . '.php';
        if (!file_exists($file)) {
            throw new RuntimeException('View not found: ' . $viewPath);
        }

        ob_start();
        extract($data, EXTR_SKIP);
        require $file;
        $content = ob_get_clean();

        $layoutFile = dirname(__DIR__) . '/views/layouts/main.php';
        if (file_exists($layoutFile)) {
            $title = $data['title'] ?? 'iTrack Zimbabwe';
            $view = $viewPath;
            $contentBlock = $content;
            require $layoutFile;
            return;
        }

        echo $content;
    }

    protected function json(array $payload): void
    {
        header('Content-Type: application/json');
        echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function requireLogin(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user'])) {
            $this->redirect('/itrack-zimbabwe/public/login.php');
        }
    }

    protected function requireRole(array $roles): void
    {
        $this->requireLogin();
        if (!in_array($_SESSION['user']['role'] ?? '', $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    protected function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function csrfToken(): string
    {
        $this->ensureSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    protected function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($this->csrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    protected function validateCsrf(): bool
    {
        $this->ensureSession();
        $token = $_POST['csrf_token'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    protected function sanitize(string $value): string
    {
        return trim(strip_tags($value));
    }

    protected function sanitizeInt(mixed $value): int
    {
        return (int) filter_var($value, FILTER_VALIDATE_INT);
    }

    protected function sanitizeFloat(mixed $value): float
    {
        return (float) filter_var($value, FILTER_VALIDATE_FLOAT);
    }
}
