<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('admin', '../login.php');

$uid = $_SESSION['user_id'];

// ── Sidebar badge counts ───────────────────────────────────────────
$pendingEvts = $pdo->query("SELECT COUNT(*) FROM events WHERE status='pending'")->fetchColumn();

$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

// ── Users data ─────────────────────────────────────────────────────
$users = $pdo->query("
    SELECT u.*,
      (SELECT COUNT(*) FROM registrations r WHERE r.student_id = u.id) AS reg_count,
      (SELECT COUNT(*) FROM events e WHERE e.organizer_id = u.id) AS event_count
    FROM users u
    ORDER BY u.created_at DESC
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
  <title>Manage Users – UniEventHub</title>
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

        <a href="users.php" class="sidebar-link active">
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
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">Manage Users</div>
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

      <div class="card">
        <div class="card-header">
          <div class="card-title">All Users (<?= count($users) ?>)</div>
        </div>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Name</th><th>Email</th><th>Role</th><th>Department</th><th>Activity</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $u): ?>
              <tr>
                <td>
                  <div style="font-weight:600;color:var(--gray-900)"><?= htmlspecialchars($u['full_name']) ?></div>
                  <div style="font-size:.75rem;color:var(--gray-400)">Joined <?= date('M Y', strtotime($u['created_at'])) ?></div>
                </td>
                <td style="font-size:.85rem"><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <span class="badge <?= ['admin'=>'badge-danger','organizer'=>'badge-info','student'=>'badge-primary'][$u['role']] ?? 'badge-gray' ?>">
                    <?= ucfirst($u['role']) ?>
                  </span>
                </td>
                <td style="font-size:.85rem"><?= htmlspecialchars($u['department'] ?? '—') ?></td>
                <td style="font-size:.82rem;color:var(--gray-500)">
                  <?php if ($u['role'] === 'student'): ?>
                    <?= $u['reg_count'] ?> registration<?= $u['reg_count'] != 1 ? 's' : '' ?>
                  <?php elseif ($u['role'] === 'organizer'): ?>
                    <?= $u['event_count'] ?> event<?= $u['event_count'] != 1 ? 's' : '' ?>
                  <?php else: ?>
                    System Admin
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge <?= $u['is_active'] ? 'badge-success' : 'badge-gray' ?>">
                    <?= $u['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td>
                  <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                  <a href="../php/toggle_user.php?id=<?= $u['id'] ?>"
                     class="btn btn-sm <?= $u['is_active'] ? 'btn-warning' : 'btn-success' ?>">
                    <?= $u['is_active'] ? 'Deactivate' : 'Activate' ?>
                  </a>
                  <?php else: ?>
                  <span style="font-size:.78rem;color:var(--gray-400)">You</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
