<?php
session_start();
require_once __DIR__ . '/app/helpers/auth.php';

if (!isLoggedIn()) {
    header('Location: /itrack-zimbabwe/public/login.php');
    exit;
}

header('Location: /itrack-zimbabwe/public/dashboard.php');
exit;
