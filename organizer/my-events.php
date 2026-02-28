<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('organizer', '../login.php');

$uid = $_SESSION['user_id'];

// ── Sidebar badge counts ───────────────────────────────────────────
$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

// ── Page data ─────────────────────────────────────────────────────
$stmt = $pdo->prepare("
    SELECT e.*,
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count,
        (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id AND r.attendance = 1) AS attended_count
    FROM events e
    WHERE e.organizer_id = ?
    ORDER BY e.event_date DESC
");
$stmt->execute([$uid]);
$allEvents = $stmt->fetchAll();

$pendingList   = array_values(array_filter($allEvents, fn($e) => $e['status'] === 'pending'));
$approvedList  = array_values(array_filter($allEvents, fn($e) => $e['status'] === 'approved'));
$rejectedList  = array_values(array_filter($allEvents, fn($e) => $e['status'] === 'rejected'));
$cancelledList = array_values(array_filter($allEvents, fn($e) => $e['status'] === 'cancelled'));

$totalRegs = array_sum(array_column($allEvents, 'reg_count'));

$user = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$uid]); $user = $user->fetch();

$flash = getFlash();
$activePage = 'my-events';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Events – UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">

  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content">
    <!-- Topbar -->
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">My Events</div>
      </div>
      <div style="display:flex;align-items:center;gap:14px">
        <!-- Bell icon topbar -->
        <a href="notifications.php" style="position:relative;display:flex;align-items:center;color:var(--gray-500);text-decoration:none" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadNotif > 0): ?>
            <span style="position:absolute;top:-5px;right:-5px;min-width:16px;height:16px;background:var(--danger);color:white;border-radius:50%;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px"><?= $unreadNotif ?></span>
          <?php endif; ?>
        </a>
        <a href="create-event.php" class="btn btn-primary btn-sm">+ Create Event</a>
      </div>
    </div>

    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <!-- Stat cards -->
      <div class="stat-cards">
        <div class="stat-card">
          <div class="stat-card-icon icon-purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          </div>
          <div><div class="stat-card-num"><?= count($allEvents) ?></div><div class="stat-card-lbl">Total Events</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          </div>
          <div><div class="stat-card-num"><?= count($approvedList) ?></div><div class="stat-card-lbl">Approved</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
          </div>
          <div><div class="stat-card-num"><?= count($pendingList) ?></div><div class="stat-card-lbl">Pending Approval</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-card-icon icon-blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
          </div>
          <div><div class="stat-card-num"><?= $totalRegs ?></div><div class="stat-card-lbl">Total Registrations</div></div>
        </div>
      </div>

      <!-- Filter tabs -->
      <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
        <button class="btn btn-primary btn-sm tab-btn active" data-tab="all">All (<?= count($allEvents) ?>)</button>
        <button class="btn btn-secondary btn-sm tab-btn" data-tab="approved">Approved (<?= count($approvedList) ?>)</button>
        <button class="btn btn-secondary btn-sm tab-btn" data-tab="pending">Pending (<?= count($pendingList) ?>)</button>
        <button class="btn btn-secondary btn-sm tab-btn" data-tab="rejected">Rejected (<?= count($rejectedList) ?>)</button>
      </div>

      <!-- All Events table -->
      <?php
      $tabs = [
        'all'      => $allEvents,
        'approved' => $approvedList,
        'pending'  => $pendingList,
        'rejected' => $rejectedList,
      ];
      $statusColors = ['approved'=>'badge-success','pending'=>'badge-warning','rejected'=>'badge-danger','cancelled'=>'badge-gray'];
      foreach ($tabs as $tabKey => $tabEvents):
      ?>
      <div class="tab-content" id="tab-<?= $tabKey ?>" style="<?= $tabKey !== 'all' ? 'display:none' : '' ?>">
        <div class="card">
          <div class="card-header">
            <div class="card-title"><?= ucfirst($tabKey) ?> Events</div>
            <a href="create-event.php" class="btn btn-primary btn-sm">+ New Event</a>
          </div>
          <?php if (empty($tabEvents)): ?>
          <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            <h3>No <?= $tabKey ?> events</h3>
            <?php if ($tabKey === 'all'): ?><p><a href="create-event.php">Create your first event</a></p><?php endif; ?>
          </div>
          <?php else: ?>
          <div class="table-wrap">
            <table>
              <thead>
                <tr><th>Event Title</th><th>Category</th><th>Date</th><th>Venue</th><th>Registrations</th><th>Fee</th><th>Status</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($tabEvents as $ev): ?>
                <tr>
                  <td>
                    <div style="font-weight:600;color:var(--gray-900)"><?= htmlspecialchars($ev['title']) ?></div>
                    <div style="font-size:.75rem;color:var(--gray-400)">Created <?= date('M j, Y', strtotime($ev['created_at'])) ?></div>
                  </td>
                  <td><span class="badge badge-gray" style="font-size:.75rem"><?= ucfirst(str_replace('_',' ',$ev['category'])) ?></span></td>
                  <td>
                    <div><?= date('M j, Y', strtotime($ev['event_date'])) ?></div>
                    <div style="font-size:.75rem;color:var(--gray-400)"><?= date('g:i A', strtotime($ev['start_time'])) ?></div>
                  </td>
                  <td style="font-size:.875rem"><?= htmlspecialchars($ev['venue'] ?: 'TBA') ?></td>
                  <td>
                    <div style="display:flex;align-items:center;gap:6px">
                      <div style="flex:1;height:6px;background:var(--gray-100);border-radius:3px;overflow:hidden">
                        <?php $pct = $ev['max_participants'] > 0 ? ($ev['reg_count'] / $ev['max_participants']) * 100 : 0; ?>
                        <div style="height:100%;background:<?= $pct >= 90 ? 'var(--danger)' : ($pct >= 60 ? 'var(--warning)' : 'var(--success)') ?>;border-radius:3px;width:<?= min(100,$pct) ?>%"></div>
                      </div>
                      <span style="font-size:.78rem;font-weight:600;white-space:nowrap"><?= $ev['reg_count'] ?> / <?= $ev['max_participants'] ?></span>
                    </div>
                  </td>
                  <td style="font-weight:600;color:var(--primary)">
                    <?= $ev['registration_fee'] > 0 ? '₹'.number_format($ev['registration_fee'],2) : '<span style="color:var(--success)">Free</span>' ?>
                  </td>
                  <td><span class="badge <?= $statusColors[$ev['status']] ?? 'badge-gray' ?>"><?= ucfirst($ev['status']) ?></span></td>
                  <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap">
                      <a href="edit-event.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                      <a href="participants.php?id=<?= $ev['id'] ?>" class="btn btn-secondary btn-sm" title="View Participants">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <?= $ev['reg_count'] ?>
                      </a>
                      <a href="../php/delete_event.php?id=<?= $ev['id'] ?>" class="btn btn-danger btn-sm"
                         onclick="return confirm('Delete this event permanently?')">Delete</a>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>

    </div>
  </main>
</div>
<script src="../js/main.js"></script>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('btn-primary');
      b.classList.add('btn-secondary');
      b.classList.remove('active');
    });
    this.classList.add('btn-primary');
    this.classList.remove('btn-secondary');
    this.classList.add('active');
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    document.getElementById('tab-' + this.dataset.tab).style.display = '';
  });
});
</script>
</body>
</html>
