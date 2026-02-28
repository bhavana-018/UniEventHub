<?php
// php/save_event.php - Create or Update events
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireRole('organizer', '../login.php');

$action       = $_POST['action'] ?? 'create';
$event_id     = (int)($_POST['event_id'] ?? 0);
$uid          = $_SESSION['user_id'];

$title            = trim($_POST['title']          ?? '');
$category         = $_POST['category']            ?? 'other';
$venue            = trim($_POST['venue']           ?? '');
$event_date       = $_POST['event_date']           ?? '';
$start_time       = $_POST['start_time']           ?? '';
$end_time         = $_POST['end_time']             ?? '';
$max_participants = (int)($_POST['max_participants'] ?? 100);
$registration_fee = (float)($_POST['registration_fee'] ?? 0);
$poster_url       = trim($_POST['poster_url']      ?? '');
$description      = trim($_POST['description']     ?? '');
$eligibility      = trim($_POST['eligibility']     ?? '');

$allowed_cats = ['seminar','workshop','cultural','sports','technical','guest_lecture','other'];
if (!in_array($category, $allowed_cats)) $category = 'other';

if (empty($title)) { setFlash('error', 'Event title is required.'); redirect('../organizer/'.($action==='create'?'create-event.php':'edit-event.php?id='.$event_id)); }
if (empty($event_date) || empty($start_time)) { setFlash('error', 'Event date and time are required.'); redirect('../organizer/'.($action==='create'?'create-event.php':'edit-event.php?id='.$event_id)); }

if ($action === 'create') {
    $stmt = $pdo->prepare("INSERT INTO events (organizer_id, title, category, venue, event_date, start_time, end_time, max_participants, registration_fee, poster_url, description, eligibility) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([$uid, $title, $category, $venue, $event_date, $start_time, $end_time ?: null, $max_participants, $registration_fee, $poster_url ?: null, $description, $eligibility]);
    setFlash('success', 'Event submitted for admin approval!');
} else {
    // Verify ownership
    $check = $pdo->prepare("SELECT id FROM events WHERE id=? AND organizer_id=?");
    $check->execute([$event_id, $uid]);
    if (!$check->fetch()) { setFlash('error', 'Unauthorized.'); redirect('../organizer/dashboard.php'); }

    $stmt = $pdo->prepare("UPDATE events SET title=?, category=?, venue=?, event_date=?, start_time=?, end_time=?, max_participants=?, registration_fee=?, poster_url=?, description=?, eligibility=? WHERE id=? AND organizer_id=?");
    $stmt->execute([$title, $category, $venue, $event_date, $start_time, $end_time ?: null, $max_participants, $registration_fee, $poster_url ?: null, $description, $eligibility, $event_id, $uid]);
    setFlash('success', 'Event updated successfully!');
}

redirect('../organizer/dashboard.php');
