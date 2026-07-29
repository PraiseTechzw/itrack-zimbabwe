<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class AccountingController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('accounting/index', ['title' => 'Accounting']);
    }
}
