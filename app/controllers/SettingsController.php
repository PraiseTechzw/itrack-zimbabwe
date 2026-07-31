<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class SettingsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('settings/index', ['title' => 'Settings']);
    }
}
