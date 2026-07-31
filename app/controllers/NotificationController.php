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
        $this->requireLogin();
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
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=notification');
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
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=notification');
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
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=notification');
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

        $this->redirect('/itrack-zimbabwe/public/index.php?controller=notification');
    }
}
