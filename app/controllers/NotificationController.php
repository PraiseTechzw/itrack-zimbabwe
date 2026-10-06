<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/Notification.php';

class NotificationController extends Controller
{
    private Notification $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new Notification();
    }

    public function index(): void
    {
        $this->requireModuleAccess('notification', ['Administrator','Director','Finance Officer','Procurement Officer','Store Officer','Sales Officer','Technician','Staff']);
        $userId = $_SESSION['user']['id'] ?? null;

        $notifications = $this->notificationModel->all($userId);
        $unreadCount = $this->notificationModel->unreadCount($userId);
        $totalCount = $this->notificationModel->countAll($userId);
        $readCount = $totalCount - $unreadCount;

        $this->view('notifications/index', [
            'title' => 'Notifications',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'readCount' => $readCount,
            'totalCount' => $totalCount,
        ]);
    }

    public function create(): void
    {
        $this->requireLogin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('notifications/form', [
                    'title' => 'Create Notification',
                    'mode' => 'create',
                    'error' => 'Invalid security token',
                    'notification' => $_POST,
                ]);
                return;
            }

            $this->notificationModel->create($_POST);
            $this->redirect('/index.php?controller=notification');
        }

        $this->view('notifications/form', [
            'title' => 'Create Notification',
            'mode' => 'create',
            'notification' => [],
        ]);
    }

    public function edit(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);
        $notification = $this->notificationModel->find($id);

        if (!$notification) {
            $this->redirect('/index.php?controller=notification');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('notifications/form', [
                    'title' => 'Edit Notification',
                    'mode' => 'edit',
                    'error' => 'Invalid security token',
                    'notification' => $notification,
                ]);
                return;
            }

            $this->notificationModel->update($id, $_POST);
            $this->redirect('/index.php?controller=notification');
        }

        $this->view('notifications/form', [
            'title' => 'Edit Notification',
            'mode' => 'edit',
            'notification' => $notification,
        ]);
    }

    public function delete(): void
    {
        $this->requireLogin();
        $id = $this->sanitizeInt($_GET['id'] ?? 0);

        if ($id > 0) {
            $this->notificationModel->delete($id);
        }

        $this->redirect('/index.php?controller=notification');
    }

    public function apiList(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user']['id'] ?? null;
        $notifications = $this->notificationModel->unreadForUser($userId !== null ? (int) $userId : null);

        $this->json([
            'notifications' => array_map(static fn (array $notification): array => [
                'id' => (int) ($notification['id'] ?? 0),
                'title' => (string) ($notification['title'] ?? ''),
                'message' => (string) ($notification['message'] ?? ''),
                'created_at' => (string) ($notification['created_at'] ?? ''),
            ], $notifications),
            'count' => $this->notificationModel->unreadCount((int) $userId),
        ]);
    }

    public function markRead(): void
    {
        $this->requireLogin();
        $userId = $_SESSION['user']['id'] ?? null;
        if ($userId === null) {
            $this->json(['ok' => false]);
            return;
        }

        $incomingIds = $_POST['ids'] ?? [];
        $ids = is_array($incomingIds) ? array_map('intval', $incomingIds) : [];
        $this->notificationModel->markRead((int) $userId, $ids);

        $this->json([
            'ok' => true,
            'count' => $this->notificationModel->unreadCount((int) $userId),
        ]);
    }
}
