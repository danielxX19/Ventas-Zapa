<?php
session_start();

// Credenciales demo: cámbialas luego
const ADMIN_USER = 'admin';
const ADMIN_PASS = 'thn123';

function isAdminLogged(): bool {
    return isset($_SESSION['admin_logged']) && $_SESSION['admin_logged'] === true;
}

function requireAdmin(): void {
    if (!isAdminLogged()) {
        header('Location: admin_login.php');
        exit;
    }
}

function loginAdmin(string $user, string $pass): bool {
    if ($user === ADMIN_USER && $pass === ADMIN_PASS) {
        $_SESSION['admin_logged'] = true;
        return true;
    }
    return false;
}

function logoutAdmin(): void {
    unset($_SESSION['admin_logged']);
}