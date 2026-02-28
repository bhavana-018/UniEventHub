<?php
// php/register.php
header('Content-Type: application/json');
require_once '../includes/db.php';
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$full_name        = trim($_POST['full_name']        ?? '');
$email            = trim($_POST['email']            ?? '');
$password         = $_POST['password']              ?? '';
$confirm_password = $_POST['confirm_password']      ?? '';
$role             = $_POST['role']                  ?? 'student';
$department       = trim($_POST['department']       ?? '');
$phone            = trim($_POST['phone']            ?? '');

// Validation
if (empty($full_name) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}
if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}
if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}
if (!in_array($role, ['student', 'organizer'])) {
    $role = 'student';
}

// Check duplicate email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'An account with this email already exists.']);
    exit;
}

// Hash password & insert
$hashed = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, department, phone) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$full_name, $email, $hashed, $role, $department, $phone]);

echo json_encode([
    'success'  => true,
    'message'  => 'Account created successfully!',
    'redirect' => '../login.php'
]);











