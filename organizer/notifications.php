<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('organizer','../login.php');

$uid = $_SESSION['user_id'];

// Count BEFORE marking as read
$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadNotif->execute([$uid]); $unreadBefore = $unreadNotif->fetchColumn();

// Mark all as read on page open
$pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$uid]);

$unreadNotif = 0; // now marked read
$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
$unreadMsg->execute([$uid]); $unreadMsg=$unreadMsg->fetchColumn();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user=$user->fetch();

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC");
$notifs->execute([$uid]); $notifs=$notifs->fetchAll();

$total  = count($notifs);
$read   = count(array_filter($notifs, fn($n) => $n['is_read']));

$activePage = 'notifications';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Notifications — UniEventHub</title>
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
        <div class="topbar-title">Notifications</div>
      </div>
      <?php if ($total > 0): ?>
      <button class="btn btn-ghost btn-sm" onclick="clearAll()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
        Clear All
      </button>
      <?php endif; ?>
    </div>

    <div class="page-content">

      <!-- Stats -->
      <div class="stat-cards" style="grid-template-columns:repeat(3,1fr);margin-bottom:22px">
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $total ?></div><div class="stat-card-lbl">Total</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $unreadBefore ?></div><div class="stat-card-lbl">Were Unread</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $read ?></div><div class="stat-card-lbl">Read</div></div>
        </div>
      </div>

      <!-- Notification List -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            All Notifications
          </div>
          <span class="badge badge-primary badge-nodot"><?= $total ?> total</span>
        </div>

        <?php if (empty($notifs)): ?>
        <div class="empty-state">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <h3>No notifications yet</h3>
          <p>You'll receive updates about your events here</p>
        </div>
        <?php else: ?>
        <?php foreach ($notifs as $n): ?>
        <div style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-bottom:1px solid var(--border);transition:var(--t);<?= !$n['is_read'] ? 'background:rgba(99,102,241,.04)' : '' ?>" onmouseenter="this.style.background='rgba(99,102,241,.06)'" onmouseleave="this.style.background='<?= !$n['is_read'] ? 'rgba(99,102,241,.04)' : '' ?>'">
          <div style="width:36px;height:36px;border-radius:50%;background:<?= $n['is_read'] ? 'var(--bg-4)' : 'var(--indigo-soft)' ?>;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="<?= $n['is_read'] ? 'var(--text-5)' : 'var(--indigo-light)' ?>" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          </div>
          <div style="flex:1;min-width:0">
            <div style="font-size:.84rem;color:var(--text-1);line-height:1.5"><?= htmlspecialchars($n['message']) ?></div>
            <div style="font-size:.72rem;color:var(--text-5);margin-top:4px">
              <?= date('M j, Y · g:i A', strtotime($n['created_at'])) ?>
            </div>
          </div>
          <span class="badge badge-nodot <?= $n['is_read'] ? 'badge-gray' : 'badge-primary' ?>">
            <?= $n['is_read'] ? 'Read' : 'New' ?>
          </span>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script src="../js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', initSidebar);

async function clearAll() {
  if (!confirm('Clear all notifications?')) return;
  try {
    const res  = await fetch('../php/clear_notifications.php');
    const text = await res.text();
    let data;
    try { data = JSON.parse(text); } catch(e) {
      console.error('Non-JSON response:', text);
      showToast('Server error – please refresh and try again.', 'error');
      return;
    }
    if (data.success) {
      window.location.reload();
    } else {
      showToast('Could not clear: ' + (data.message || 'Unknown error'), 'error');
    }
  } catch(e) {
    console.error('Fetch error:', e);
    showToast('Network error – check your connection.', 'error');
  }
}
</script>
</body>
</html>
