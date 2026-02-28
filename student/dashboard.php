<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student','../login.php');

$uid = $_SESSION['user_id'];

$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$totalRegs = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id=?");
$totalRegs->execute([$uid]); $totalRegs = $totalRegs->fetchColumn();

$upcoming = $pdo->prepare("SELECT COUNT(*) FROM registrations r JOIN events e ON r.event_id=e.id WHERE r.student_id=? AND e.event_date>=CURDATE()");
$upcoming->execute([$uid]); $upcoming = $upcoming->fetchColumn();

$attended = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE student_id=? AND attendance=1");
$attended->execute([$uid]); $attended = $attended->fetchColumn();

$availEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status='approved' AND event_date>=CURDATE()")->fetchColumn();

$regs = $pdo->prepare("SELECT e.id AS event_id,e.title,e.category,e.event_date,e.start_time,e.venue,e.registration_fee,r.registered_at,r.attendance FROM registrations r JOIN events e ON r.event_id=e.id WHERE r.student_id=? ORDER BY r.registered_at DESC LIMIT 6");
$regs->execute([$uid]); $regs = $regs->fetchAll();

$notifs = $pdo->prepare("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$notifs->execute([$uid]); $notifs = $notifs->fetchAll();

$flash = getFlash();
$activePage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — UniEventHub</title>
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
        <div class="topbar-title">Student Dashboard</div>
      </div>
      <div style="display:flex;align-items:center;gap:12px">
        <a href="../events.php" class="btn btn-primary btn-sm">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
          Browse Events
        </a>
        <a href="notifications.php" style="position:relative;display:flex;color:var(--text-3);text-decoration:none;padding:6px;border-radius:var(--r-sm);transition:var(--t)" onmouseenter="this.style.background='rgba(255,255,255,.06)'" onmouseleave="this.style.background='none'">
          <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadNotif > 0): ?>
            <span style="position:absolute;top:2px;right:2px;min-width:15px;height:15px;background:var(--danger);color:#fff;border-radius:50%;font-size:.58rem;font-weight:700;display:flex;align-items:center;justify-content:center;line-height:1"><?= $unreadNotif ?></span>
          <?php endif; ?>
        </a>
      </div>
    </div>

    <!-- Page Content -->
    <div class="page-content">

      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-2"><?= htmlspecialchars($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Welcome Banner -->
      <div class="welcome-banner">
        <div style="position:relative;z-index:1">
          <div class="welcome-banner-title">Welcome back, <?= htmlspecialchars(explode(' ',$user['full_name'])[0]) ?>! 👋</div>
          <div class="welcome-banner-sub">Here's what's happening with your events today.</div>
        </div>
        <a href="../events.php" class="btn btn-primary btn-sm" style="position:relative;z-index:1">Discover Events →</a>
      </div>

      <!-- Stat Cards -->
      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $totalRegs ?></div>
            <div class="stat-card-lbl">Total Registered</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $upcoming ?></div>
            <div class="stat-card-lbl">Upcoming Events</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $attended ?></div>
            <div class="stat-card-lbl">Events Attended</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-cyan">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div>
            <div class="stat-card-num"><?= $availEvents ?></div>
            <div class="stat-card-lbl">Events Available</div>
          </div>
        </div>
      </div>

      <!-- Two column grid -->
      <div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start">

        <!-- Recent Registrations -->
        <div class="card">
          <div class="card-header">
            <div class="card-title">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              Recent Registrations
            </div>
            <a href="my-registrations.php" class="btn btn-ghost btn-sm">View All →</a>
          </div>
          <?php if (empty($regs)): ?>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
            <h3>No registrations yet</h3>
            <p><a href="../events.php">Browse events</a> to get started</p>
          </div>
          <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr>
                  <th>Event</th>
                  <th>Date</th>
                  <th>Fee</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $catEmoji=['seminar'=>'📚','workshop'=>'🔧','cultural'=>'🎭','sports'=>'⚽','technical'=>'💻','guest_lecture'=>'🎤','other'=>'🎪'];
                foreach ($regs as $r):
                ?>
                <tr>
                  <td>
                    <a href="../event-detail.php?id=<?= $r['event_id'] ?>" style="font-weight:600;color:var(--text-1);font-size:.84rem"><?= htmlspecialchars($r['title']) ?></a>
                    <div style="font-size:.72rem;color:var(--text-5);margin-top:2px"><?= $catEmoji[$r['category']]??'🎪' ?> <?= ucfirst(str_replace('_',' ',$r['category'])) ?></div>
                  </td>
                  <td style="font-size:.8rem;color:var(--text-3);white-space:nowrap"><?= date('M j, Y', strtotime($r['event_date'])) ?></td>
                  <td style="font-size:.8rem">
                    <?= $r['registration_fee']>0
                      ? '<span style="color:var(--indigo-light);font-weight:600">₹'.number_format($r['registration_fee'],2).'</span>'
                      : '<span class="badge badge-success badge-nodot">Free</span>' ?>
                  </td>
                  <td>
                    <?php if ($r['event_date'] < date('Y-m-d')): ?>
                      <span class="badge <?= $r['attendance'] ? 'badge-success' : 'badge-gray' ?> badge-nodot"><?= $r['attendance'] ? 'Attended' : 'Past' ?></span>
                    <?php else: ?>
                      <span class="badge badge-info badge-nodot">Upcoming</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>

        <!-- Notifications Panel -->
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
            <p>No new notifications</p>
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

    </div><!-- /page-content -->
  </main>
</div>

<script src="../js/main.js"></script>
<script>document.addEventListener('DOMContentLoaded', initSidebar);</script>
</body>
</html>
