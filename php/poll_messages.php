<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

requireLogin('../login.php');

$myId   = $_SESSION['user_id'];
$withId = (int)($_GET['with'] ?? 0);
$lastId = (int)($_GET['last'] ?? 0);

if (!$withId) {
    echo json_encode(['messages' => []]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT
        m.id,
        m.message,
        m.sent_at,
        u.full_name AS sender_name,
        u.role      AS sender_role,
        LEFT(u.full_name, 1) AS initial
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.sender_id   = ?
      AND m.receiver_id = ?
      AND m.id          > ?
    ORDER BY m.sent_at ASC
");
$stmt->execute([$withId, $myId, $lastId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (!empty($rows)) {
    $pdo->prepare("
        UPDATE messages
        SET is_read = 1
        WHERE sender_id = ? AND receiver_id = ? AND id > ?
    ")->execute([$withId, $myId, $lastId]);
}

$formatted = [];
foreach ($rows as $row) {
    $formatted[] = [
        'id'      => (int)$row['id'],
        'raw'     => $row['message'],                                      
        'message' => htmlspecialchars($row['message'], ENT_QUOTES, 'UTF-8'),
        'time'    => date('g:i A', strtotime($row['sent_at'])),
        'initial' => strtoupper($row['initial']),
        'role'    => $row['sender_role'],
    ];
}

echo json_encode(['messages' => $formatted]);
