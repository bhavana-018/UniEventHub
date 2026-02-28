<?php
// php/approve_event.php
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('admin', '../login.php');

$id     = (int)($_GET['id'] ?? 0);
$action = $_GET['action'] ?? '';

if (!$id || !in_array($action, ['approve', 'reject'])) {
    setFlash('error', 'Invalid request.');
    redirect('../admin/events.php');
}

$status = $action === 'approve' ? 'approved' : 'rejected';
$pdo->prepare("UPDATE events SET status=? WHERE id=?")->execute([$status, $id]);

// Notify organizer
$ev = $pdo->prepare("SELECT title, organizer_id FROM events WHERE id=?");
$ev->execute([$id]);
$ev = $ev->fetch();
if ($ev) {
    $msg = $status === 'approved'
        ? "Your event '{$ev['title']}' has been approved and is now live!"
        : "Your event '{$ev['title']}' was not approved. Please contact admin for details.";
    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?,?)")->execute([$ev['organizer_id'], $msg]);
}

setFlash('success', 'Event ' . $status . ' successfully.');
redirect('../admin/events.php');
