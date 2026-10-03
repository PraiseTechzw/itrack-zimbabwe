<?php
require_once dirname(__DIR__) . '/core/Controller.php';
class AuthMiddleware { public static function handle(): void { if (session_status()===PHP_SESSION_NONE) session_start(); if(empty($_SESSION['user'])) { header('Location: /login.php'); exit; } } }
