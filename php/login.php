<?php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['password'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
    exit;
}

if (session_status() === PHP_SESSION_NONE) session_start();
session_regenerate_id(true);

$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_role']  = $user['role'];

$redirects = [
    'admin'     => '../admin/dashboard.php',
    'organizer' => '../organizer/dashboard.php',
    'student'   => '../student/dashboard.php',
];

echo json_encode([
    'success'  => true,
    'message'  => 'Login successful',
    'redirect' => $redirects[$user['role']] ?? '../index.php'
]);







