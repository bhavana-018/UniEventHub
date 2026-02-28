<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('organizer','../login.php');

$uid = $_SESSION['user_id'];

$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$totalEvents = $pdo->prepare("SELECT COUNT(*) FROM events WHERE organizer_id=?"); $totalEvents->execute([$uid]); $totalEvents=$totalEvents->fetchColumn();
$approved    = $pdo->prepare("SELECT COUNT(*) FROM events WHERE organizer_id=? AND status='approved'"); $approved->execute([$uid]); $approved=$approved->fetchColumn();
$pending     = $pdo->prepare("SELECT COUNT(*) FROM events WHERE organizer_id=? AND status='pending'"); $pending->execute([$uid]); $pending=$pending->fetchColumn();
$totalRegs   = $pdo->prepare("SELECT COALESCE(SUM(sub.cnt),0) FROM (SELECT COUNT(*) as cnt FROM registrations r JOIN events e ON r.event_id=e.id WHERE e.organizer_id=?) sub"); $totalRegs->execute([$uid]); $totalRegs=$totalRegs->fetchColumn();

$recentEvents = $pdo->prepare("SELECT e.*,(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) AS reg_count FROM events e WHERE e.organizer_id=? ORDER BY e.created_at DESC LIMIT 5");
$recentEvents->execute([$uid]); $recentEvents=$recentEvents->fetchAll();

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]); $notifs=$notifs->fetchAll();

$flash = getFlash();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Organizer Dashboard — UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content">

    <!-- Topbar -->
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:6px;color:var(--text-3);display:flex;border-radius:var(--r-sm);transition:var(--t)" onmouseenter="this.style.background='rgba(255,255,255,.06)'" onmouseleave="this.style.background='none'">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">Organizer Dashboard</div>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <a href="create-event.php" class="btn btn-primary btn-sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Create Event
        </a>
        <a href="notifications.php" style="position:relative;display:flex;color:var(--text-3);text-decoration:none;padding:6px;border-radius:var(--r-sm);transition:var(--t)" onmouseenter="this.style.background='rgba(255,255,255,.06)'" onmouseleave="this.style.background='none'">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadNotif > 0): ?>
            <span style="position:absolute;top:2px;right:2px;min-width:15px;height:15px;background:var(--danger);color:#fff;border-radius:50%;font-size:.58rem;font-weight:700;display:flex;align-items:center;justify-content:center"><?= $unreadNotif ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-2"><?= htmlspecialchars($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <div style="position:relative;z-index:1">
          <div class="welcome-banner-title">Hello, <?= htmlspecialchars(explode(' ',$user['full_name'])[0]) ?>! 🎯</div>
          <div class="welcome-banner-sub">Manage your events and track participant registrations from here.</div>
        </div>
        <a href="create-event.php" class="btn btn-primary btn-sm" style="position:relative;z-index:1">+ Create New Event</a>
      </div>

      <!-- Stat Cards -->
      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $totalEvents ?></div>
            <div class="stat-card-lbl">Total Events</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $approved ?></div>
            <div class="stat-card-lbl">Approved</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $pending ?></div>
            <div class="stat-card-lbl">Pending Review</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-cyan">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $totalRegs ?></div>
            <div class="stat-card-lbl">Total Registrations</div>
          </div>
        </div>
      </div>

      <!-- Two column grid -->
      <div style="display:grid;grid-template-columns:1fr 310px;gap:20px;align-items:start">

        <!-- Recent Events -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              Recent Events
            </div>
            <a href="my-events.php" class="btn btn-ghost btn-sm">View All →</a>
          </div>
          <?php if (empty($recentEvents)): ?>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/></svg>
            <h3>No events yet</h3>
            <p><a href="create-event.php">Create your first event</a></p>
          </div>
          <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Event</th><th>Date</th><th>Registrations</th><th>Status</th><th></th></tr>
              </thead>
              <tbody>
                <?php foreach ($recentEvents as $e): ?>
                <tr>
                  <td>
                    <div style="font-weight:600;color:var(--text-1);font-size:.84rem"><?= htmlspecialchars($e['title']) ?></div>
                    <div style="font-size:.72rem;color:var(--text-5)"><?= ucfirst(str_replace('_',' ',$e['category'])) ?></div>
                  </td>
                  <td style="font-size:.8rem;color:var(--text-3);white-space:nowrap"><?= date('M j, Y', strtotime($e['event_date'])) ?></td>
                  <td>
                    <div style="font-weight:600;color:var(--text-1);font-size:.84rem"><?= $e['reg_count'] ?> / <?= $e['max_participants'] ?></div>
                    <div class="progress-bar" style="margin-top:5px;width:80px">
                      <div class="progress-fill" style="width:<?= $e['max_participants']>0 ? min(100,($e['reg_count']/$e['max_participants'])*100) : 0 ?>%"></div>
                    </div>
                  </td>
                  <td>
                    <span class="badge badge-nodot <?= ['approved'=>'badge-success','pending'=>'badge-warning','rejected'=>'badge-danger'][$e['status']] ?? 'badge-gray' ?>">
                      <?= ucfirst($e['status']) ?>
                    </span>
                  </td>
                  <td>
                    <a href="../event-detail.php?id=<?= $e['id'] ?>" class="btn btn-ghost btn-sm">View</a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>

        <!-- Notifications -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
              Notifications
            </div>
            <a href="notifications.php" class="btn btn-ghost btn-sm">All →</a>
          </div>
          <?php if (empty($notifs)): ?>
          <div class="empty-state" style="padding:36px 16px">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/></svg>
            <h3>All caught up!</h3>
          </div>
          <?php else: ?>
          <?php foreach ($notifs as $n): ?>
          <div class="notif-preview-item">
            <div class="notif-dot <?= $n['is_read'] ? 'read' : '' ?>"></div>
            <div style="flex:1;min-width:0">
              <div style="font-size:.8rem;color:var(--text-2);line-height:1.45"><?= htmlspecialchars($n['message']) ?></div>
              <div style="font-size:.7rem;color:var(--text-5);margin-top:3px"><?= date('M j · g:i A', strtotime($n['created_at'])) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </main>
</div>

<script src="../js/main.js"></script>
<script>document.addEventListener('DOMContentLoaded', initSidebar);</script>
</body>
</html>
