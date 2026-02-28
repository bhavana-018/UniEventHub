<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('organizer','../login.php');

$event_id = (int)($_GET['id'] ?? 0);
$uid      = $_SESSION['user_id'];

$ev = $pdo->prepare("SELECT * FROM events WHERE id=? AND organizer_id=?");
$ev->execute([$event_id, $uid]);
$ev = $ev->fetch();
if (!$ev) { setFlash('error','Event not found.'); redirect('../organizer/dashboard.php'); }

$participants = $pdo->prepare("SELECT u.full_name, u.email, u.department, u.phone, r.registered_at, r.attendance, r.payment_status
    FROM registrations r JOIN users u ON r.student_id = u.id
    WHERE r.event_id = ? ORDER BY r.registered_at ASC");
$participants->execute([$event_id]);
$participants = $participants->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Participants – UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand"><div class="brand-text"><span>UniEventHub</span></div></div>
    <nav class="sidebar-nav"><div class="sidebar-section">
      <a href="dashboard.php" class="sidebar-link active"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Dashboard</a>
    </div></nav>
    <div class="sidebar-footer"><a href="../php/logout.php" class="sidebar-link">Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <div class="topbar-title">Participants</div>
      </div>
      <a href="dashboard.php" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="page-content">
      <div style="margin-bottom:20px">
        <h2 style="font-size:1.2rem;font-weight:700"><?= htmlspecialchars($ev['title']) ?></h2>
        <div style="font-size:.875rem;color:var(--gray-500)"><?= date('M j, Y', strtotime($ev['event_date'])) ?> • <?= count($participants) ?> registered</div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">Registered Participants (<?= count($participants) ?> / <?= $ev['max_participants'] ?>)</div>
        </div>
        <?php if (empty($participants)): ?>
        <div class="empty-state"><h3>No participants yet</h3></div>
        <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>#</th><th>Name</th><th>Email</th><th>Department</th><th>Phone</th><th>Registered</th></tr></thead>
            <tbody>
              <?php foreach ($participants as $i => $p): ?>
              <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($p['full_name']) ?></td>
                <td><?= htmlspecialchars($p['email']) ?></td>
                <td><?= htmlspecialchars($p['department'] ?? '—') ?></td>
                <td><?= htmlspecialchars($p['phone'] ?? '—') ?></td>
                <td><?= date('M j, Y', strtotime($p['registered_at'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
