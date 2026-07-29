<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class PurchasesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('purchases/index', ['title' => 'Purchases']);
    }
}
