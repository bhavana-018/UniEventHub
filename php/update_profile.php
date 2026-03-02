<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin('../login.php');

$uid        = $_SESSION['user_id'];
$full_name  = trim($_POST['full_name']  ?? '');
$phone      = trim($_POST['phone']      ?? '');
$department = trim($_POST['department'] ?? '');

if (empty($full_name)) {
    setFlash('error', 'Full name is required.');
} else {
    $pdo->prepare("UPDATE users SET full_name=?, phone=?, department=? WHERE id=?")
        ->execute([$full_name, $phone, $department, $uid]);
    $_SESSION['user_name'] = $full_name;
    setFlash('success', 'Profile updated successfully.');
}

$role = $_SESSION['user_role'];
header("Location: ../{$role}/profile.php");
exit;
