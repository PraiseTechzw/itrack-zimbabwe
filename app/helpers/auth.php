<?php

function isLoggedIn(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return !empty($_SESSION['user']);
}

function currentUser(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    return $_SESSION['user'] ?? null;
}
