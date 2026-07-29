<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class RequisitionController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('requisitions/index', ['title' => 'Requisitions']);
    }
}
