<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class ClientController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('clients/index', ['title' => 'Clients']);
    }
}
