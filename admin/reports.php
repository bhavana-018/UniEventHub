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

// ── Report data ────────────────────────────────────────────────────
$catStats = $pdo->query("
    SELECT category,
           COUNT(*) AS total,
           SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved
    FROM events GROUP BY category
")->fetchAll();

$topEvents = $pdo->query("
    SELECT e.title, e.event_date, u.full_name AS organizer,
           COUNT(r.id) AS reg_count
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    LEFT JOIN registrations r ON r.event_id = e.id
    WHERE e.status = 'approved'
    GROUP BY e.id ORDER BY reg_count DESC LIMIT 10
")->fetchAll();

$monthly = $pdo->query("
    SELECT DATE_FORMAT(registered_at,'%Y-%m') AS month, COUNT(*) AS count
    FROM registrations
    GROUP BY month ORDER BY month DESC LIMIT 6
")->fetchAll();

$deptStats = $pdo->query("
    SELECT department, COUNT(*) AS count
    FROM users
    WHERE role='student' AND department IS NOT NULL AND department != ''
    GROUP BY department ORDER BY count DESC
")->fetchAll();

// ── Extra stats ────────────────────────────────────────────────────
$totalStudents   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student' AND is_active=1")->fetchColumn();
$totalOrganizers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='organizer' AND is_active=1")->fetchColumn();
$totalEvents     = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
$approvedEvents  = $pdo->query("SELECT COUNT(*) FROM events WHERE status='approved'")->fetchColumn();
$totalRegs       = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports – UniEventHub</title>
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

        <a href="reports.php" class="sidebar-link active">
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
        <div class="topbar-title">Reports &amp; Analytics</div>
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

      <!-- ── Summary stat cards ── -->
      <div class="stat-cards" style="margin-bottom:24px">
        <div class="stat-card">
          <div class="stat-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalStudents ?></div><div class="stat-card-lbl">Total Students</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalEvents ?></div><div class="stat-card-lbl">Total Events</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $approvedEvents ?></div><div class="stat-card-lbl">Approved Events</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalRegs ?></div><div class="stat-card-lbl">Total Registrations</div></div>
        </div>
      </div>

      <!-- ── Row 1: Events by Category + Students by Department ── -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">

        <!-- Events by Category -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Events by Category</div>
          </div>
          <div class="card-body" style="padding:20px">
            <?php if (empty($catStats)): ?>
            <div style="text-align:center;padding:24px;color:var(--gray-400)">No event data yet</div>
            <?php else: ?>
            <?php foreach ($catStats as $c): ?>
            <div style="margin-bottom:18px">
              <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:.875rem">
                <span style="font-weight:600;color:var(--gray-800)"><?= ucfirst(str_replace('_',' ',$c['category'])) ?></span>
                <span style="color:var(--gray-500)"><?= $c['total'] ?> event<?= $c['total'] != 1 ? 's' : '' ?></span>
              </div>
              <div style="height:10px;background:var(--gray-100);border-radius:5px;overflow:hidden">
                <div style="height:100%;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:5px;width:<?= $c['total'] > 0 ? min(100, ($c['approved'] / $c['total']) * 100) : 0 ?>%;transition:width .5s ease"></div>
              </div>
              <div style="font-size:.75rem;color:var(--gray-400);margin-top:3px"><?= $c['approved'] ?> approved</div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Students by Department -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">Students by Department</div>
          </div>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Department</th><th>Students</th></tr></thead>
              <tbody>
                <?php if (empty($deptStats)): ?>
                <tr><td colspan="2" style="text-align:center;color:var(--gray-400);padding:24px">No department data</td></tr>
                <?php else: ?>
                <?php foreach ($deptStats as $d): ?>
                <tr>
                  <td style="font-weight:500"><?= htmlspecialchars($d['department']) ?></td>
                  <td><span class="badge badge-primary"><?= $d['count'] ?></span></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- ── Top Events by Registration ── -->
      <div class="card" style="margin-bottom:24px">
        <div class="card-header">
          <div class="card-title">Top Events by Registration</div>
          <a href="events.php" class="btn btn-secondary btn-sm">View All Events</a>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Event</th><th>Organizer</th><th>Date</th><th>Registrations</th></tr></thead>
            <tbody>
              <?php if (empty($topEvents)): ?>
              <tr><td colspan="5" style="text-align:center;color:var(--gray-400);padding:24px">No approved events yet</td></tr>
              <?php else: ?>
              <?php foreach ($topEvents as $i => $ev): ?>
              <tr>
                <td style="color:var(--gray-400);font-weight:600"><?= $i + 1 ?></td>
                <td style="font-weight:600;color:var(--gray-900)"><?= htmlspecialchars($ev['title']) ?></td>
                <td><?= htmlspecialchars($ev['organizer']) ?></td>
                <td><?= date('M j, Y', strtotime($ev['event_date'])) ?></td>
                <td><span class="badge badge-success"><?= $ev['reg_count'] ?></span></td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Monthly Registrations ── -->
      <div class="card">
        <div class="card-header">
          <div class="card-title">Monthly Registrations (Last 6 months)</div>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Month</th><th>Registrations</th><th style="width:40%">Visual</th></tr></thead>
            <tbody>
              <?php if (empty($monthly)): ?>
              <tr><td colspan="3" style="text-align:center;color:var(--gray-400);padding:24px">No registration data yet</td></tr>
              <?php else: ?>
              <?php $max = max(array_column($monthly,'count') ?: [1]); ?>
              <?php foreach ($monthly as $m): ?>
              <tr>
                <td style="font-weight:500"><?= $m['month'] ?></td>
                <td><strong><?= $m['count'] ?></strong></td>
                <td>
                  <div style="height:12px;background:var(--gray-100);border-radius:6px;overflow:hidden">
                    <div style="height:100%;background:linear-gradient(90deg,var(--primary),var(--secondary));border-radius:6px;width:<?= ($m['count'] / $max) * 100 ?>%;transition:width .5s ease"></div>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php endif; ?>
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
