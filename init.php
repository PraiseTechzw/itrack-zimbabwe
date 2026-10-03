<?php
session_start();
require_once __DIR__ . '/app/helpers/auth.php';

if (!isLoggedIn()) {
    header('Location: /login.php');
    exit;
}

header('Location: /dashboard.php');
exit;
