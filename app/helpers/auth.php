<?php

require_once dirname(__DIR__) . '/core/Session.php';

function isLoggedIn(): bool
{
    Session::start();

    return !empty($_SESSION['user']);
}

function currentUser(): ?array
{
    Session::start();

    return $_SESSION['user'] ?? null;
}
