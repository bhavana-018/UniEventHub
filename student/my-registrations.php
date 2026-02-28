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

$regs = $pdo->prepare("SELECT e.id AS event_id,e.title,e.category,e.event_date,e.start_time,e.venue,e.registration_fee,r.registered_at,r.attendance,r.id AS reg_id FROM registrations r JOIN events e ON r.event_id=e.id WHERE r.student_id=? ORDER BY e.event_date DESC");
$regs->execute([$uid]); $regs=$regs->fetchAll();

$upcoming = array_filter($regs, fn($r) => $r['event_date'] >= date('Y-m-d'));
$past     = array_filter($regs, fn($r) => $r['event_date'] < date('Y-m-d'));

$flash = getFlash();
$activePage = 'my-registrations';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Registrations — UniEventHub</title>
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
        <div class="topbar-title">My Registrations</div>
      </div>
      <a href="../events.php" class="btn btn-primary btn-sm">+ Browse More Events</a>
    </div>

    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-2"><?= htmlspecialchars($flash['message']) ?></div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="stat-cards" style="grid-template-columns:repeat(3,1fr);margin-bottom:22px">
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg>
          </div>
          <div><div class="stat-card-num"><?= count($regs) ?></div><div class="stat-card-lbl">Total</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div><div class="stat-card-num"><?= count($upcoming) ?></div><div class="stat-card-lbl">Upcoming</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div><div class="stat-card-num"><?= count(array_filter($regs, fn($r) => $r['attendance'])) ?></div><div class="stat-card-lbl">Attended</div></div>
        </div>
      </div>

      <?php if (empty($regs)): ?>
      <div class="card">
        <div class="empty-state" style="padding:72px 24px">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <h3>No registrations yet</h3>
          <p><a href="../events.php">Browse events</a> and register to see them here</p>
        </div>
      </div>
      <?php else: ?>

      <!-- Upcoming -->
      <?php if (!empty($upcoming)): ?>
      <div class="card" style="margin-bottom:20px">
        <div class="card-header">
          <div class="card-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            Upcoming Events
          </div>
          <span class="badge badge-info badge-nodot"><?= count($upcoming) ?></span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Event</th><th>Date &amp; Time</th><th>Venue</th><th>Fee</th><th>Action</th></tr></thead>
            <tbody>
              <?php foreach ($upcoming as $r): ?>
              <tr>
                <td>
                  <a href="../event-detail.php?id=<?= $r['event_id'] ?>" style="font-weight:600;color:var(--text-1);font-size:.84rem"><?= htmlspecialchars($r['title']) ?></a>
                  <div style="font-size:.72rem;color:var(--text-5)"><?= ucfirst(str_replace('_',' ',$r['category'])) ?></div>
                </td>
                <td style="font-size:.8rem;color:var(--text-3);white-space:nowrap">
                  <?= date('M j, Y', strtotime($r['event_date'])) ?><br>
                  <span style="font-size:.72rem;color:var(--text-5)"><?= date('g:i A', strtotime($r['start_time'])) ?></span>
                </td>
                <td style="font-size:.8rem;color:var(--text-3)"><?= htmlspecialchars($r['venue'] ?: 'TBA') ?></td>
                <td style="font-size:.8rem">
                  <?= $r['registration_fee']>0
                    ? '<span style="color:var(--indigo-light);font-weight:600">₹'.number_format($r['registration_fee'],2).'</span>'
                    : '<span class="badge badge-success badge-nodot">Free</span>' ?>
                </td>
                <td>
                  <button onclick="cancelReg(<?= $r['reg_id'] ?>)" class="btn btn-danger btn-sm">Cancel</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>

      <!-- Past -->
      <?php if (!empty($past)): ?>
      <div class="card">
        <div class="card-header">
          <div class="card-title">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 8 12 12 14 14"/></svg>
            Past Events
          </div>
          <span class="badge badge-gray badge-nodot"><?= count($past) ?></span>
        </div>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Event</th><th>Date</th><th>Fee</th><th>Attendance</th></tr></thead>
            <tbody>
              <?php foreach ($past as $r): ?>
              <tr>
                <td>
                  <a href="../event-detail.php?id=<?= $r['event_id'] ?>" style="font-weight:600;color:var(--text-1);font-size:.84rem"><?= htmlspecialchars($r['title']) ?></a>
                  <div style="font-size:.72rem;color:var(--text-5)"><?= ucfirst(str_replace('_',' ',$r['category'])) ?></div>
                </td>
                <td style="font-size:.8rem;color:var(--text-3);white-space:nowrap"><?= date('M j, Y', strtotime($r['event_date'])) ?></td>
                <td style="font-size:.8rem">
                  <?= $r['registration_fee']>0
                    ? '<span style="color:var(--indigo-light);font-weight:600">₹'.number_format($r['registration_fee'],2).'</span>'
                    : '<span class="badge badge-success badge-nodot">Free</span>' ?>
                </td>
                <td>
                  <span class="badge badge-nodot <?= $r['attendance'] ? 'badge-success' : 'badge-gray' ?>">
                    <?= $r['attendance'] ? '✓ Attended' : 'Absent' ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endif; ?>
      <?php endif; ?>

    </div>
  </main>
</div>

<script src="../js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', initSidebar);

async function cancelReg(id) {
  if (!confirm('Cancel this registration?')) return;
  try {
    const fd = new FormData();
    fd.append('reg_id', id);
    const res  = await fetch('../php/cancel_registration.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      showToast('Registration cancelled.', 'success');
      setTimeout(() => window.location.reload(), 900);
    } else {
      showToast(data.message || 'Failed to cancel.', 'error');
    }
  } catch(e) {
    showToast('Network error.', 'error');
  }
}
</script>
</body>
</html>
