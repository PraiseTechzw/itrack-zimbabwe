<?php
require_once dirname(__DIR__) . '/core/Session.php';
class AuthMiddleware { public static function handle(): void { Session::start(); if(empty($_SESSION['user'])) { header('Location: /login.php'); exit; } } }
