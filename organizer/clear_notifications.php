<?php
// php/clear_notifications.php
// Returns JSON — called via fetch() from all notification pages
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin('../login.php');

$uid = $_SESSION['user_id'];

$stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
$stmt->execute([$uid]);

echo json_encode(['success' => true, 'message' => 'All notifications cleared.']);
