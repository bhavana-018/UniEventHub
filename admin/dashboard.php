<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('admin', '../login.php');

$uid = $_SESSION['user_id'];

// ── System-wide stats ──────────────────────────────────────────────
$totalUsers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$totalEvents = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$pendingEvts = $pdo->query("SELECT COUNT(*) FROM events WHERE status='pending'")->fetchColumn();
$totalRegs   = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

// ── Unread counts for sidebar badges ──────────────────────────────
$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

// ── Recent notifications preview (NOT marked read here) ───────────
$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]); $notifications = $notifs->fetchAll();

// ── Recent events ──────────────────────────────────────────────────
$recentEvents = $pdo->query("
    SELECT e.*, u.full_name AS organizer_name
    FROM events e JOIN users u ON e.organizer_id = u.id
    ORDER BY e.created_at DESC LIMIT 8
")->fetchAll();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard – UniEventHub</title>
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

        <a href="dashboard.php" class="sidebar-link active">
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

        <!-- Messages with unread badge -->
        <a href="messages.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          Messages
          <?php if ($unreadMsg > 0): ?>
            <span class="badge badge-danger" style="margin-left:auto;font-size:.7rem;padding:2px 7px"><?= $unreadMsg ?></span>
          <?php endif; ?>
        </a>

        <!-- Notifications with unread badge -->
        <a href="notifications.php" class="sidebar-link">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          Notifications
          <?php if ($unreadNotif > 0): ?>
            <span class="badge badge-danger" style="margin-left:auto;font-size:.7rem;padding:2px 7px"><?= $unreadNotif ?></span>
          <?php endif; ?>
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

    <!-- Topbar -->
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">Admin Dashboard</div>
      </div>
      <div style="display:flex;align-items:center;gap:14px">
        <!-- Bell icon with badge -->
        <a href="notifications.php" style="position:relative;display:flex;align-items:center;color:var(--gray-500);text-decoration:none" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadNotif > 0): ?>
            <span style="position:absolute;top:-5px;right:-5px;min-width:16px;height:16px;background:var(--danger);color:white;border-radius:50%;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px"><?= $unreadNotif ?></span>
          <?php endif; ?>
        </a>
        <?php if ($pendingEvts > 0): ?>
        <a href="events.php?filter=pending" class="badge badge-warning" style="padding:6px 14px;text-decoration:none">
          ⏳ <?= $pendingEvts ?> Pending Approval<?= $pendingEvts > 1 ? 's' : '' ?>
        </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="page-content">

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- ── Stat Cards ── -->
      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalUsers ?></div><div class="stat-card-lbl">Students</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalEvents ?></div><div class="stat-card-lbl">Total Events</div></div>
        </div>
        <div class="stat-card" style="cursor:pointer" onclick="window.location='events.php?filter=pending'">
          <div class="stat-card-icon icon-yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $pendingEvts ?></div><div class="stat-card-lbl">Pending Approvals</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalRegs ?></div><div class="stat-card-lbl">Registrations</div></div>
        </div>
      </div>

      <!-- ── Two-column layout ── -->
      <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">

        <!-- Recent Events table -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Recent Events</div>
            <a href="events.php" class="btn btn-primary btn-sm">View All</a>
          </div>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Title</th><th>Organizer</th><th>Date</th><th>Status</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($recentEvents as $ev): ?>
                <tr>
                  <td>
                    <div style="font-weight:600;color:var(--gray-900)"><?= htmlspecialchars($ev['title']) ?></div>
                    <div style="font-size:.78rem;color:var(--gray-400)"><?= ucfirst(str_replace('_',' ',$ev['category'])) ?></div>
                  </td>
                  <td><?= htmlspecialchars($ev['organizer_name']) ?></td>
                  <td><?= date('M j, Y', strtotime($ev['event_date'])) ?></td>
                  <td>
                    <?php $sc=['approved'=>'badge-success','pending'=>'badge-warning','rejected'=>'badge-danger','cancelled'=>'badge-gray']; ?>
                    <span class="badge <?= $sc[$ev['status']] ?? 'badge-gray' ?>"><?= ucfirst($ev['status']) ?></span>
                  </td>
                  <td style="display:flex;gap:4px;flex-wrap:wrap">
                    <?php if ($ev['status'] === 'pending'): ?>
                    <a href="../php/approve_event.php?id=<?= $ev['id'] ?>&action=approve" class="btn btn-success btn-sm">Approve</a>
                    <a href="../php/approve_event.php?id=<?= $ev['id'] ?>&action=reject"  class="btn btn-danger btn-sm">Reject</a>
                    <?php endif; ?>
                    <a href="../php/delete_event.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm" onclick="return confirm('Delete this event?')">Delete</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Notifications panel -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              🔔 Notifications
              <?php if ($unreadNotif > 0): ?>
                <span class="badge badge-danger" style="margin-left:6px"><?= $unreadNotif ?> new</span>
              <?php endif; ?>
            </div>
            <a href="notifications.php" class="btn btn-secondary btn-sm">View All</a>
          </div>

          <?php if (empty($notifications)): ?>
          <div style="padding:32px 16px;text-align:center;color:var(--gray-400)">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 10px;display:block"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <div style="font-size:.875rem">No notifications yet</div>
          </div>
          <?php else: ?>
          <div>
            <?php foreach ($notifications as $n): ?>
            <div style="
              display:flex; align-items:flex-start; gap:12px;
              padding:13px 18px; border-bottom:1px solid var(--gray-100);
              background:<?= !$n['is_read'] ? '#F5F3FF' : 'transparent' ?>;
            ">
              <div style="
                width:34px; height:34px; border-radius:50%; flex-shrink:0;
                background:<?= !$n['is_read'] ? 'var(--primary)' : 'var(--gray-100)' ?>;
                display:flex; align-items:center; justify-content:center; margin-top:2px;
              ">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="<?= !$n['is_read'] ? 'white' : 'var(--gray-400)' ?>" stroke-width="2">
                  <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                  <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
              </div>
              <div style="flex:1;min-width:0">
                <div style="
                  font-size:.855rem;
                  font-weight:<?= !$n['is_read'] ? '600' : '400' ?>;
                  color:<?= !$n['is_read'] ? 'var(--gray-900)' : 'var(--gray-600)' ?>;
                  margin-bottom:3px; word-break:break-word;
                "><?= htmlspecialchars($n['message']) ?></div>
                <div style="font-size:.72rem;color:var(--gray-400)">
                  <?= date('M j, g:i A', strtotime($n['created_at'])) ?>
                </div>
              </div>
              <?php if (!$n['is_read']): ?>
              <div style="width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:5px"></div>
              <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div style="padding:12px 18px;text-align:center;border-top:1px solid var(--gray-100)">
              <a href="notifications.php" style="font-size:.82rem;color:var(--primary);font-weight:600;text-decoration:none">
                View all notifications →
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </main>
</div>
<script src="../js/main.js"></script>
</body>
</html>