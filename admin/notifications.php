<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('admin', '../login.php');

$uid = $_SESSION['user_id'];

// ── Sidebar badge counts ───────────────────────────────────────────
$pendingEvts = $pdo->query("SELECT COUNT(*) FROM events WHERE status='pending'")->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

// ── Notification data ─────────────────────────────────────────────
$totalNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ?");
$totalNotif->execute([$uid]); $totalNotif = $totalNotif->fetchColumn();

$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$broadcastCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1")->fetchColumn();

// Fetch all notifications
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$notifs->execute([$uid]); $notifications = $notifs->fetchAll();

// Mark all as read
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications – UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">

  <!-- ══════════════ SIDEBAR ══════════════ -->
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div>
        <div class="brand-text"><span>UniEventHub</span></div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.3)">Admin Panel</div>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-section">
        <div class="sidebar-section-label">Management</div>

        <a href="dashboard.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
          Dashboard
        </a>

        <a href="events.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Manage Events
          <?php if ($pendingEvts > 0): ?>
            <span class="badge badge-warning" style="margin-left:auto;font-size:.7rem;padding:2px 7px"><?= $pendingEvts ?></span>
          <?php endif; ?>
        </a>

        <a href="users.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
          Manage Users
        </a>

        <a href="reports.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
          Reports
        </a>

        <a href="messages.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Messages
          <?php if ($unreadMsg > 0): ?>
            <span class="badge badge-danger" style="margin-left:auto;font-size:.7rem;padding:2px 7px"><?= $unreadMsg ?></span>
          <?php endif; ?>
        </a>

        <a href="notifications.php" class="sidebar-link active">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          Notifications
        </a>
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar">A</div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name"><?= htmlspecialchars($user['full_name']) ?></div>
          <div class="sidebar-user-role">Administrator</div>
        </div>
      </div>
      <a href="../php/logout.php" class="sidebar-link" style="margin-top:4px">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        Logout
      </a>
    </div>
  </aside>

  <!-- ══════════════ MAIN ══════════════ -->
  <main class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">Notifications</div>
      </div>
      <!-- Send Broadcast button -->
      <button class="btn btn-primary" onclick="openModal('broadcast-modal')" style="display:flex;align-items:center;gap:8px">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
        Send Broadcast
      </button>
    </div>

    <div class="page-content">

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- ── Stat cards ── -->
      <div class="stat-cards" style="margin-bottom:24px">
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalNotif ?></div><div class="stat-card-lbl">My Notifications</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $unreadNotif ?></div><div class="stat-card-lbl">Unread</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $broadcastCount ?></div><div class="stat-card-lbl">Students to Broadcast</div></div>
        </div>
      </div>

      <!-- ── Notifications list ── -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">My Notifications</div>
          <?php if (!empty($notifications)): ?>
          <button onclick="clearAll()" class="btn btn-secondary btn-sm">Clear All</button>
          <?php endif; ?>
        </div>

        <?php if (empty($notifications)): ?>
        <div class="empty-state" style="padding:60px 20px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <h3>No notifications</h3>
          <p>System notifications will appear here.</p>
        </div>
        <?php else: ?>
        <div>
          <?php foreach ($notifications as $n): ?>
          <div style="
            display:flex; align-items:flex-start; gap:14px;
            padding:16px 24px; border-bottom:1px solid var(--gray-100);
          ">
            <div style="
              width:38px; height:38px; border-radius:50%; flex-shrink:0;
              background:var(--primary-light);
              display:flex; align-items:center; justify-content:center;
            ">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
            </div>
            <div style="flex:1">
              <div style="font-size:.875rem;font-weight:500;color:var(--gray-800);margin-bottom:4px;word-break:break-word">
                <?= htmlspecialchars($n['message']) ?>
              </div>
              <div style="font-size:.75rem;color:var(--gray-400)">
                <?= date('M j, Y · g:i A', strtotime($n['created_at'])) ?>
              </div>
            </div>
            <span class="badge badge-success" style="flex-shrink:0">Read</span>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- ── Broadcast Modal ── -->
<div class="modal-overlay" id="broadcast-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">📢 Send Broadcast Notification</div>
      <button class="modal-close" data-modal-close="broadcast-modal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Send To</label>
        <select id="broadcast-target" class="form-control">
          <option value="all">All Users (Students + Organizers)</option>
          <option value="student">All Students only</option>
          <option value="organizer">All Organizers only</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Message</label>
        <textarea id="broadcast-msg" class="form-control" rows="4"
          placeholder="Type your broadcast message here..."></textarea>
      </div>
      <div id="broadcast-alert" style="display:none"></div>
      <div style="display:flex;gap:10px">
        <button class="btn btn-primary" style="flex:1" id="broadcast-btn" onclick="sendBroadcast()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3zm-8.27 4a2 2 0 0 1-3.46 0"/></svg>
          Send Broadcast
        </button>
        <button class="btn btn-secondary" data-modal-close="broadcast-modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script src="../js/main.js"></script>
<script>
async function sendBroadcast() {
  const target  = document.getElementById('broadcast-target').value;
  const message = document.getElementById('broadcast-msg').value.trim();
  const alertEl = document.getElementById('broadcast-alert');
  const btn     = document.getElementById('broadcast-btn');

  if (!message) {
    alertEl.className = 'alert alert-danger';
    alertEl.innerHTML = '❌ Please enter a message.';
    alertEl.style.display = 'flex';
    return;
  }

  btn.disabled    = true;
  btn.textContent = 'Sending...';

  try {
    const fd = new FormData();
    fd.append('message', message);
    fd.append('target',  target);
    const res  = await fetch('../php/send_broadcast.php', { method: 'POST', body: fd });
    const data = await res.json();

    if (data.success) {
      alertEl.className = 'alert alert-success';
      alertEl.innerHTML = '✅ Broadcast sent to ' + data.count + ' user(s)!';
      alertEl.style.display = 'flex';
      document.getElementById('broadcast-msg').value = '';
      btn.disabled    = false;
      btn.textContent = 'Send Broadcast';
      setTimeout(() => { window.location.reload(); }, 1500);
    } else {
      alertEl.className = 'alert alert-danger';
      alertEl.innerHTML = '❌ ' + (data.message || 'Failed to send.');
      alertEl.style.display = 'flex';
      btn.disabled    = false;
      btn.textContent = 'Send Broadcast';
    }
  } catch (e) {
    alertEl.className = 'alert alert-danger';
    alertEl.innerHTML = '❌ Network error. Please try again.';
    alertEl.style.display = 'flex';
    btn.disabled    = false;
    btn.textContent = 'Send Broadcast';
  }
}

async function clearAll() {
  if (!confirm('Clear all your notifications?')) return;
  try {
    const res  = await fetch('../php/clear_notifications.php');
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) {
      console.error('Non-JSON response:', text);
      alert('Server error – please refresh and try again.');
      return;
    }
    if (data.success) {
      window.location.reload();
    } else {
      alert('Could not clear: ' + (data.message || 'Unknown error'));
    }
  } catch(e) {
    console.error('Fetch error:', e);
    alert('Network error – please check your connection.');
  }
}
</script>
</body>
</html>
