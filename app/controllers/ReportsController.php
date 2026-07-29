<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class ReportsController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('reports/index', ['title' => 'Reports']);
    }
}
