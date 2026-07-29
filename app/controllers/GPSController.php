<?php

require_once dirname(__DIR__) . '/core/Controller.php';

class GPSController extends Controller
{
    public function index(): void
    {
        $this->requireLogin();
        $this->view('gps/index', ['title' => 'GPS Devices']);
    }
}
