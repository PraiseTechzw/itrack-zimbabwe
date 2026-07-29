<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class UsersController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('users/index', ['title' => 'User Management']);
    }
}
