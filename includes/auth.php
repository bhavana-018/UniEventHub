<?php
// includes/auth.php - Authentication Helpers

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(string $redirect = '../index.php'): void {
    if (!isLoggedIn()) {
        header("Location: $redirect");
        exit;
    }
}

function requireRole(string $role, string $redirect = '../index.php'): void {
    requireLogin($redirect);
    if ($_SESSION['user_role'] !== $role) {
        header("Location: $redirect");
        exit;
    }
}

function getCurrentUser(): array {
    return [
        'id'       => $_SESSION['user_id']   ?? null,
        'name'     => $_SESSION['user_name'] ?? '',
        'email'    => $_SESSION['user_email'] ?? '',
        'role'     => $_SESSION['user_role'] ?? '',
    ];
}

function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
?>
