<?php
// php/register_event.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'student') {
    echo json_encode(['success' => false, 'message' => 'Please login as a student to register.']);
    exit;
}

$event_id   = (int)($_POST['event_id'] ?? 0);
$student_id = $_SESSION['user_id'];

if (!$event_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid event.']);
    exit;
}

// Check event exists and is approved
$stmt = $pdo->prepare("SELECT * FROM events WHERE id = ? AND status = 'approved'");
$stmt->execute([$event_id]);
$ev = $stmt->fetch();
if (!$ev) {
    echo json_encode(['success' => false, 'message' => 'Event not found or not available.']);
    exit;
}

// Check if event date has passed
if ($ev['event_date'] < date('Y-m-d')) {
    echo json_encode(['success' => false, 'message' => 'Registration is closed for past events.']);
    exit;
}

// Check already registered
$check = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND student_id = ?");
$check->execute([$event_id, $student_id]);
if ($check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You are already registered for this event.']);
    exit;
}

// Check capacity
$count = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE event_id = ?");
$count->execute([$event_id]);
if ($count->fetchColumn() >= $ev['max_participants']) {
    echo json_encode(['success' => false, 'message' => 'Sorry, this event is full.']);
    exit;
}

// Register
$payStatus = $ev['registration_fee'] > 0 ? 'pending' : 'free';
$stmt = $pdo->prepare("INSERT INTO registrations (event_id, student_id, payment_status) VALUES (?, ?, ?)");
$stmt->execute([$event_id, $student_id, $payStatus]);

// Create notification
$msg = "You have successfully registered for '{$ev['title']}' on ".date('M j, Y', strtotime($ev['event_date'])).".";
$notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
$notif->execute([$student_id, $msg]);

echo json_encode(['success' => true, 'message' => 'Successfully registered for the event!']);
