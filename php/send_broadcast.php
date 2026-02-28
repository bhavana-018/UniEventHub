<?php
// php/send_broadcast.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('admin', '../login.php');

$message = trim($_POST['message'] ?? '');
$target  = $_POST['target'] ?? 'all';

if (empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty.']);
    exit;
}

if (!in_array($target, ['all', 'student', 'organizer'])) {
    $target = 'all';
}

// Get target users
if ($target === 'all') {
    $stmt = $pdo->query("SELECT id FROM users WHERE is_active = 1");
} else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = ? AND is_active = 1");
    $stmt->execute([$target]);
}

$users = $stmt->fetchAll();

if (empty($users)) {
    echo json_encode(['success' => false, 'message' => 'No users found for the selected group.']);
    exit;
}

// Insert notification for each user
$insert = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
$count  = 0;
foreach ($users as $u) {
    $insert->execute([$u['id'], $message]);
    $count++;
}

$targetLabel = $target === 'all' ? 'all users' : "all {$target}s";
echo json_encode([
    'success' => true,
    'message' => "Notification sent to {$count} {$targetLabel} successfully."
]);
