<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class SalesController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('sales/index', ['title' => 'Sales']);
    }
}
