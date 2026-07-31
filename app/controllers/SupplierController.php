<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class SupplierController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('suppliers/index', ['title' => 'Suppliers']);
    }
}
