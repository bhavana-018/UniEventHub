<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('organizer', '../login.php');

$uid = $_SESSION['user_id'];

// ── Sidebar badge counts ───────────────────────────────────────────
$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$flash = getFlash();
$activePage = 'profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile – UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">

  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">My Profile</div>
      </div>
      <div style="display:flex;align-items:center;gap:14px">
        <a href="notifications.php" style="position:relative;display:flex;align-items:center;color:var(--gray-500);text-decoration:none" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadNotif > 0): ?>
            <span style="position:absolute;top:-5px;right:-5px;min-width:16px;height:16px;background:var(--danger);color:white;border-radius:50%;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px"><?= $unreadNotif ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <div style="max-width:640px">

        <!-- Profile Info card -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Update Profile</div>
          </div>
          <div class="card-body">
            <!-- Avatar display -->
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:1px solid var(--gray-100)">
              <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));display:flex;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;color:white;flex-shrink:0">
                <?= strtoupper(substr($user['full_name'],0,1)) ?>
              </div>
              <div>
                <div style="font-weight:700;font-size:1.05rem;color:var(--gray-900)"><?= htmlspecialchars($user['full_name']) ?></div>
                <div style="font-size:.85rem;color:var(--gray-400)"><?= htmlspecialchars($user['email']) ?></div>
                <div style="margin-top:4px"><span class="badge badge-info">Organizer</span></div>
              </div>
            </div>

            <form action="../php/update_profile.php" method="POST">
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Full Name</label>
                  <input type="text" name="full_name" class="form-control"
                    value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Phone</label>
                  <input type="tel" name="phone" class="form-control"
                    value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Email <span style="color:var(--gray-400);font-size:.8rem">(read only)</span></label>
                <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
              </div>
              <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                  <?php foreach ([
                    'Computer Science','Information Technology','Electronics & Communication',
                    'Mechanical Engineering','Civil Engineering','Business Administration',
                    'Arts & Humanities','Science','Other'
                  ] as $d): ?>
                  <option value="<?= $d ?>" <?= $user['department']===$d?'selected':'' ?>><?= $d ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
          </div>
        </div>

        <!-- Change Password card -->
        <div class="card" style="margin-top:20px">
          <div class="card-header">
            <div class="card-title">Change Password</div>
          </div>
          <div class="card-body">
            <form action="../php/change_password.php" method="POST">
              <div class="form-group">
                <label class="form-label">Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">New Password</label>
                  <input type="password" name="new_password" id="new_password" class="form-control" required>
                  <div class="password-strength" id="strength-bars">
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                    <div class="strength-bar"></div>
                  </div>
                </div>
                <div class="form-group">
                  <label class="form-label">Confirm New Password</label>
                  <input type="password" name="confirm_password" class="form-control" required>
                </div>
              </div>
              <button type="submit" class="btn btn-primary">Update Password</button>
            </form>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>
<script src="../js/main.js"></script>
<script>initPasswordStrength('new_password','strength-bars');</script>
</body>
</html>
