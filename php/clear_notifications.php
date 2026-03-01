<?php

// Start session  ─────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

//JSON header ────────────────────────────────────────
header('Content-Type: application/json');

ob_start();

require_once '../includes/db.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    ob_end_clean();
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

$uid = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = ?");
    $stmt->execute([$uid]);
    $deleted = $stmt->rowCount();

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'deleted' => $deleted,
        'message' => 'All notifications cleared.'
    ]);
} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
