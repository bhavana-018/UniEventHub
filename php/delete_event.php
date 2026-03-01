<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin('../login.php');

$id  = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];

if ($id) {
    if ($_SESSION['user_role'] === 'admin') {
        $pdo->prepare("DELETE FROM events WHERE id=?")->execute([$id]);
    } else {
        $pdo->prepare("DELETE FROM events WHERE id=? AND organizer_id=?")->execute([$id, $uid]);
    }
    setFlash('success', 'Event deleted.');
}

$role = $_SESSION['user_role'];
header("Location: ../{$role}/dashboard.php");
exit;
