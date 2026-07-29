<?php
session_start();
require_once dirname(__DIR__) . '/app/controllers/DashboardController.php';

$controller = new DashboardController();
$controller->index();
