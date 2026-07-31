<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class NotificationController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('notifications/index', ['title' => 'Notifications']);
    }
}
