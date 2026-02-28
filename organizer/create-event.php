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

$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

$flash = getFlash();
$activePage = 'create-event';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Event – UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">

  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">Create New Event</div>
      </div>
      <div style="display:flex;align-items:center;gap:14px">
        <a href="notifications.php" style="position:relative;display:flex;align-items:center;color:var(--gray-500);text-decoration:none" title="Notifications">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
          <?php if ($unreadNotif > 0): ?>
            <span style="position:absolute;top:-5px;right:-5px;min-width:16px;height:16px;background:var(--danger);color:white;border-radius:50%;font-size:.62rem;font-weight:700;display:flex;align-items:center;justify-content:center;padding:0 3px"><?= $unreadNotif ?></span>
          <?php endif; ?>
        </a>
        <a href="my-events.php" class="btn btn-secondary btn-sm">← My Events</a>
      </div>
    </div>

    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
      <?php endif; ?>

      <div style="max-width:760px">
        <div class="card">
          <div class="card-header">
            <div class="card-title">Event Details</div>
            <div style="font-size:.8rem;color:var(--gray-400)">Fields marked <span style="color:var(--danger)">*</span> are required</div>
          </div>
          <div class="card-body">
            <form action="../php/save_event.php" method="POST">
              <input type="hidden" name="action" value="create">

              <div class="form-group">
                <label class="form-label">Event Title <span style="color:var(--danger)">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g., Annual Tech Symposium 2025" required>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Category <span style="color:var(--danger)">*</span></label>
                  <select name="category" class="form-control" required>
                    <option value="">Select category</option>
                    <option value="seminar">Seminar</option>
                    <option value="workshop">Workshop</option>
                    <option value="cultural">Cultural</option>
                    <option value="sports">Sports</option>
                    <option value="technical">Technical</option>
                    <option value="guest_lecture">Guest Lecture</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Venue</label>
                  <input type="text" name="venue" class="form-control" placeholder="e.g., Main Auditorium">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Event Date <span style="color:var(--danger)">*</span></label>
                  <input type="date" name="event_date" class="form-control" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Start Time <span style="color:var(--danger)">*</span></label>
                  <input type="time" name="start_time" class="form-control" required>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">End Time</label>
                  <input type="time" name="end_time" class="form-control">
                </div>
                <div class="form-group">
                  <label class="form-label">Max Participants</label>
                  <input type="number" name="max_participants" class="form-control" value="100" min="1" max="10000">
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Registration Fee (₹)</label>
                  <input type="number" name="registration_fee" class="form-control" value="0" min="0" step="0.01" placeholder="0 for free">
                </div>
                <div class="form-group">
                  <label class="form-label">Poster URL (optional)</label>
                  <input type="url" name="poster_url" class="form-control" placeholder="https://...">
                </div>
              </div>

              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"
                  placeholder="Describe the event, what participants can expect..."></textarea>
              </div>

              <div class="form-group">
                <label class="form-label">Eligibility Criteria</label>
                <textarea name="eligibility" class="form-control" rows="3"
                  placeholder="Who can participate? Any prerequisites?"></textarea>
              </div>

              <div style="display:flex;gap:10px;margin-top:8px">
                <button type="submit" class="btn btn-primary btn-lg">Submit for Approval</button>
                <a href="my-events.php" class="btn btn-secondary btn-lg">Cancel</a>
              </div>
              <div class="form-hint mt-1">Your event will be reviewed by the admin before going live.</div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>
<script src="../js/main.js"></script>
</body>
</html>
