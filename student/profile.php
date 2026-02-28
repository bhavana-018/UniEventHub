<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student','../login.php');

$uid = $_SESSION['user_id'];

$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadNotif->execute([$uid]); $unreadNotif=$unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
$unreadMsg->execute([$uid]); $unreadMsg=$unreadMsg->fetchColumn();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user=$user->fetch();

$flash = getFlash();
$activePage = 'profile';
$initials = strtoupper(substr($user['full_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Profile — UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:6px;color:var(--text-3);display:flex;border-radius:var(--r-sm);transition:var(--t)" onmouseenter="this.style.background='rgba(255,255,255,.06)'" onmouseleave="this.style.background='none'">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">My Profile</div>
      </div>
    </div>

    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-2"><?= htmlspecialchars($flash['message']) ?></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start">

        <!-- Profile Info -->
        <div class="card">
          <!-- Avatar Banner -->
          <div style="background:linear-gradient(135deg,rgba(99,102,241,.2),rgba(6,182,212,.1));padding:28px 24px;display:flex;align-items:center;gap:16px;border-bottom:1px solid var(--border)">
            <div style="width:60px;height:60px;border-radius:50%;background:linear-gradient(135deg,var(--indigo),var(--violet));display:flex;align-items:center;justify-content:center;font-family:'Plus Jakarta Sans',sans-serif;font-size:1.5rem;font-weight:800;color:white;flex-shrink:0;box-shadow:0 0 20px var(--indigo-glow)">
              <?= $initials ?>
            </div>
            <div>
              <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;color:var(--text-1)"><?= htmlspecialchars($user['full_name']) ?></div>
              <div style="font-size:.78rem;color:var(--text-4);margin-top:3px"><?= htmlspecialchars($user['email']) ?></div>
              <span class="badge badge-primary badge-nodot" style="margin-top:6px">Student</span>
            </div>
          </div>

          <div class="card-header">
            <div class="card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              Account Details
            </div>
          </div>
          <div class="card-body">
            <div id="info-alert" style="display:none;margin-bottom:16px"></div>
            <form id="info-form">
              <div class="form-group">
                <label class="form-label">Full Name</label>
                <div class="input-icon-wrap">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  <input type="text" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name']) ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+91 98765 43210">
              </div>
              <div class="form-group">
                <label class="form-label">Department</label>
                <select name="department" class="form-control">
                  <option value="">Select Department</option>
                  <?php
                  $depts = ['Computer Science','Information Technology','Electronics & Communication','Mechanical Engineering','Civil Engineering','Business Administration','Arts & Humanities','Science','Other'];
                  foreach ($depts as $d):
                  ?>
                  <option <?= ($user['department']??'')===$d?'selected':'' ?>><?= $d ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label class="form-label">Email Address</label>
                <div class="input-icon-wrap">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                  <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
                </div>
                <div class="form-hint">Email cannot be changed</div>
              </div>
              <button type="submit" class="btn btn-primary btn-block" id="info-btn">
                <span id="info-btn-text">Save Changes</span>
                <span id="info-spinner" class="spinner hidden"></span>
              </button>
            </form>
          </div>
        </div>

        <!-- Change Password -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              Change Password
            </div>
          </div>
          <div class="card-body">
            <div id="pw-alert" style="display:none;margin-bottom:16px"></div>
            <form id="pw-form">
              <div class="form-group">
                <label class="form-label">Current Password</label>
                <div class="input-icon-wrap">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  <input type="password" name="current_password" id="cur-pw" class="form-control" placeholder="Enter current password" required>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">New Password</label>
                <div class="input-icon-wrap">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  <input type="password" name="new_password" id="new-pw" class="form-control" placeholder="Min. 8 characters" required>
                </div>
                <div class="password-strength" id="pw-strength-bars">
                  <div class="strength-bar"></div><div class="strength-bar"></div>
                  <div class="strength-bar"></div><div class="strength-bar"></div>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Confirm New Password</label>
                <div class="input-icon-wrap">
                  <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  <input type="password" name="confirm_password" id="conf-pw" class="form-control" placeholder="Re-enter new password" required>
                </div>
              </div>
              <button type="submit" class="btn btn-secondary btn-block" id="pw-btn">
                <span id="pw-btn-text">Update Password</span>
                <span id="pw-spinner" class="spinner hidden"></span>
              </button>
            </form>

            <!-- Account info card -->
            <div style="margin-top:24px;background:var(--bg-3);border:1px solid var(--border);border-radius:var(--r);padding:16px">
              <div style="font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-4);margin-bottom:12px">Account Info</div>
              <div style="display:flex;flex-direction:column;gap:8px">
                <div style="display:flex;justify-content:space-between;font-size:.8rem">
                  <span style="color:var(--text-4)">Role</span>
                  <span class="badge badge-primary badge-nodot">Student</span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.8rem">
                  <span style="color:var(--text-4)">Member Since</span>
                  <span style="color:var(--text-2)"><?= date('M Y', strtotime($user['created_at'])) ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.8rem">
                  <span style="color:var(--text-4)">Department</span>
                  <span style="color:var(--text-2)"><?= htmlspecialchars($user['department'] ?: 'Not set') ?></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="../js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', initSidebar);
initPasswordStrength('new-pw','pw-strength-bars');

document.getElementById('info-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = document.getElementById('info-btn');
  const txt = document.getElementById('info-btn-text');
  const spn = document.getElementById('info-spinner');
  btn.disabled=true; txt.textContent='Saving…'; spn.classList.remove('hidden');
  try {
    const res  = await fetch('../php/update_profile.php', {method:'POST',body:new FormData(this)});
    const data = await res.json();
    const al   = document.getElementById('info-alert');
    al.className = data.success ? 'alert alert-success' : 'alert alert-danger';
    al.innerHTML = (data.success?'✓ ':'✕ ') + data.message;
    al.style.display='flex';
    if (data.success) setTimeout(()=>window.location.reload(),1200);
  } catch(e) { showToast('Server error.','error'); }
  finally { btn.disabled=false; txt.textContent='Save Changes'; spn.classList.add('hidden'); }
});

document.getElementById('pw-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const p1 = document.getElementById('new-pw').value;
  const p2 = document.getElementById('conf-pw').value;
  if (p1 !== p2) { showToast('Passwords do not match.','error'); return; }
  if (p1.length < 8) { showToast('Password must be at least 8 characters.','error'); return; }
  const btn = document.getElementById('pw-btn');
  const txt = document.getElementById('pw-btn-text');
  const spn = document.getElementById('pw-spinner');
  btn.disabled=true; txt.textContent='Updating…'; spn.classList.remove('hidden');
  try {
    const res  = await fetch('../php/change_password.php', {method:'POST',body:new FormData(this)});
    const data = await res.json();
    const al   = document.getElementById('pw-alert');
    al.className = data.success ? 'alert alert-success' : 'alert alert-danger';
    al.innerHTML = (data.success?'✓ ':'✕ ') + data.message;
    al.style.display='flex';
    if (data.success) this.reset();
  } catch(e) { showToast('Server error.','error'); }
  finally { btn.disabled=false; txt.textContent='Update Password'; spn.classList.add('hidden'); }
});
</script>
</body>
</html>
