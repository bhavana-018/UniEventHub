<?php
// php/cancel_registration.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('student', '../login.php');

$event_id   = (int)($_GET['event_id'] ?? 0);
$student_id = $_SESSION['user_id'];

if ($event_id) {
    $stmt = $pdo->prepare("DELETE FROM registrations WHERE event_id = ? AND student_id = ?");
    $stmt->execute([$event_id, $student_id]);
    setFlash('success', 'Registration cancelled successfully.');
} else {
    setFlash('error', 'Invalid event.');
}

header('Location: ../event-detail.php?id='.$event_id);
exit;
