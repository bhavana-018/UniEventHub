<?php
// php/send_message.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin('../login.php');

$senderId   = $_SESSION['user_id'];
$receiverId = (int)trim($_POST['receiver_id'] ?? 0);
$message    = trim($_POST['message'] ?? '');

// Validate
if (!$receiverId || empty($message)) {
    echo json_encode(['success' => false, 'message' => 'Recipient and message are required.']);
    exit;
}

if (mb_strlen($message) > 5000) {
    echo json_encode(['success' => false, 'message' => 'Message is too long (max 5000 characters).']);
    exit;
}

// Cannot message yourself
if ($receiverId === $senderId) {
    echo json_encode(['success' => false, 'message' => 'You cannot message yourself.']);
    exit;
}

// Check receiver exists and is active
$recv = $pdo->prepare("SELECT id FROM users WHERE id = ? AND is_active = 1");
$recv->execute([$receiverId]);
if (!$recv->fetch()) {
    echo json_encode(['success' => false, 'message' => 'Recipient not found or inactive.']);
    exit;
}

// Insert message
$stmt = $pdo->prepare("
    INSERT INTO messages (sender_id, receiver_id, message)
    VALUES (?, ?, ?)
");
$stmt->execute([$senderId, $receiverId, $message]);
$newId = $pdo->lastInsertId();

echo json_encode([
    'success'    => true,
    'id'         => (int)$newId,   // used by frontend lastId tracker
    'message_id' => $newId,
    'message'    => 'Message sent successfully.'
]);
