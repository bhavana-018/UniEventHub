<?php

require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin('../login.php');

$uid              = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password']     ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$role             = $_SESSION['user_role'];

$stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
$stmt->execute([$uid]);
$user = $stmt->fetch();

if (!password_verify($current_password, $user['password'])) {
    setFlash('error', 'Current password is incorrect.');
} elseif (strlen($new_password) < 8) {
    setFlash('error', 'New password must be at least 8 characters.');
} elseif ($new_password !== $confirm_password) {
    setFlash('error', 'New passwords do not match.');
} else {
    $hashed = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed, $uid]);
    setFlash('success', 'Password changed successfully.');
}

header("Location: ../{$role}/profile.php");
exit;
