<?php

require_once dirname(__DIR__) . '/core/Controller.php';
require_once dirname(__DIR__) . '/models/User.php';

class UsersController extends Controller
{
    private User $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    public function index(): void
    {
        $this->requireLogin();
        $this->view('users/index', ['title' => 'User Management', 'users' => $this->userModel->all()]);
    }

    public function create(): void
    {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->validateCsrf()) {
                $this->view('users/form', ['title' => 'Create User', 'error' => 'Invalid security token']);
                return;
            }
            $this->userModel->create($_POST);
            $this->redirect('/itrack-zimbabwe/public/index.php?controller=users');
        }

        $this->view('users/form', ['title' => 'Create User', 'mode' => 'create']);
    }
}
