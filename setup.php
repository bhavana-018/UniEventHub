<?php
// setup.php - Run once to create default accounts. DELETE after use!
require_once 'includes/db.php';

// Check if already set up
$count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($count > 0) {
    die('<div style="font-family:sans-serif;padding:30px;max-width:500px;margin:50px auto;background:#FEF3C7;border-radius:12px;border:2px solid #F59E0B"><h2>⚠️ Already Set Up</h2><p>Default accounts already exist. This script cannot run again.</p><p><strong>Please delete setup.php from your server immediately for security.</strong></p><a href="login.php" style="color:#4F46E5">Go to Login →</a></div>');
}

$adminHash = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 12]);
$orgHash   = password_hash('Organizer@123', PASSWORD_BCRYPT, ['cost' => 12]);

$pdo->prepare("INSERT INTO users (full_name, email, password, role, department) VALUES (?,?,?,?,?)")
    ->execute(['System Administrator', 'admin@unieventhub.com', $adminHash, 'admin', 'Administration']);

$pdo->prepare("INSERT INTO users (full_name, email, password, role, department) VALUES (?,?,?,?,?)")
    ->execute(['Demo Organizer', 'organizer@unieventhub.com', $orgHash, 'organizer', 'Computer Science']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Setup Complete – UniEventHub</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;background:linear-gradient(135deg,#4F46E5,#0EA5E9)">
  <div style="background:white;padding:40px;border-radius:20px;max-width:480px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2)">
    <h1 style="font-size:1.5rem;font-weight:800;color:#111827;margin-bottom:8px">✅ Setup Complete!</h1>
    <p style="color:#6B7280;margin-bottom:24px">UniEventHub default accounts created successfully.</p>
    <div style="background:#F3F4F6;border-radius:10px;padding:20px;margin-bottom:24px">
      <div style="margin-bottom:16px">
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#9CA3AF;margin-bottom:4px">Admin Account</div>
        <div style="font-weight:600">admin@unieventhub.com</div>
        <div style="color:#6B7280;font-size:.875rem">Password: <code>Admin@123</code></div>
      </div>
      <div>
        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;color:#9CA3AF;margin-bottom:4px">Organizer Account</div>
        <div style="font-weight:600">organizer@unieventhub.com</div>
        <div style="color:#6B7280;font-size:.875rem">Password: <code>Organizer@123</code></div>
      </div>
    </div>
    <div style="background:#FEF2F2;border:1px solid #FECACA;border-radius:10px;padding:16px;margin-bottom:24px">
      <p style="color:#991B1B;font-size:.875rem;margin:0"><strong>⚠️ Security Warning:</strong> Delete <code>setup.php</code> from your server immediately after this step!</p>
    </div>
    <a href="login.php" style="display:block;text-align:center;background:#4F46E5;color:white;padding:12px;border-radius:10px;font-weight:600;text-decoration:none">Go to Login →</a>
  </div>
</body>
</html>
