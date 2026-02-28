<?php
// php/toggle_user.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('admin','../login.php');

$id = (int)($_GET['id'] ?? 0);
if ($id && $id !== $_SESSION['user_id']) {
    $stmt = $pdo->prepare("SELECT is_active FROM users WHERE id=?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if ($user) {
        $new = $user['is_active'] ? 0 : 1;
        $pdo->prepare("UPDATE users SET is_active=? WHERE id=?")->execute([$new, $id]);
        setFlash('success', 'User status updated.');
    }
}
redirect('../admin/users.php');
