<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('organizer','../login.php');

$id  = (int)($_GET['id'] ?? 0);
$uid = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM events WHERE id=? AND organizer_id=?");
$stmt->execute([$id, $uid]);
$ev = $stmt->fetch();
if (!$ev) { setFlash('error','Event not found.'); redirect('../organizer/dashboard.php'); }
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Event – UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="dashboard-layout">
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand"><div class="brand-text"><span>UniEventHub</span></div></div>
    <nav class="sidebar-nav"><div class="sidebar-section">
      <a href="dashboard.php" class="sidebar-link"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>Dashboard</a>
    </div></nav>
    <div class="sidebar-footer"><a href="../php/logout.php" class="sidebar-link">Logout</a></div>
  </aside>
  <main class="main-content">
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:12px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:4px"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg></button>
        <div class="topbar-title">Edit Event</div>
      </div>
      <a href="dashboard.php" class="btn btn-secondary btn-sm">← Back</a>
    </div>
    <div class="page-content">
      <?php if ($flash): ?>
      <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3"><?= htmlspecialchars($flash['message']) ?></div>
      <?php endif; ?>
      <div style="max-width:760px">
        <div class="card">
          <div class="card-header"><div class="card-title">Edit: <?= htmlspecialchars($ev['title']) ?></div></div>
          <div class="card-body">
            <form action="../php/save_event.php" method="POST">
              <input type="hidden" name="action" value="edit">
              <input type="hidden" name="event_id" value="<?= $ev['id'] ?>">

              <div class="form-group">
                <label class="form-label">Event Title *</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($ev['title']) ?>" required>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Category *</label>
                  <select name="category" class="form-control" required>
                    <?php foreach (['seminar','workshop','cultural','sports','technical','guest_lecture','other'] as $c): ?>
                    <option value="<?= $c ?>" <?= $ev['category']===$c?'selected':'' ?>><?= ucfirst(str_replace('_',' ',$c)) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group">
                  <label class="form-label">Venue</label>
                  <input type="text" name="venue" class="form-control" value="<?= htmlspecialchars($ev['venue'] ?? '') ?>">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Event Date *</label>
                  <input type="date" name="event_date" class="form-control" value="<?= $ev['event_date'] ?>" required>
                </div>
                <div class="form-group">
                  <label class="form-label">Start Time *</label>
                  <input type="time" name="start_time" class="form-control" value="<?= $ev['start_time'] ?>" required>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">End Time</label>
                  <input type="time" name="end_time" class="form-control" value="<?= $ev['end_time'] ?? '' ?>">
                </div>
                <div class="form-group">
                  <label class="form-label">Max Participants</label>
                  <input type="number" name="max_participants" class="form-control" value="<?= $ev['max_participants'] ?>" min="1">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group">
                  <label class="form-label">Registration Fee (₹)</label>
                  <input type="number" name="registration_fee" class="form-control" value="<?= $ev['registration_fee'] ?>" min="0" step="0.01">
                </div>
                <div class="form-group">
                  <label class="form-label">Poster URL</label>
                  <input type="url" name="poster_url" class="form-control" value="<?= htmlspecialchars($ev['poster_url'] ?? '') ?>">
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($ev['description'] ?? '') ?></textarea>
              </div>
              <div class="form-group">
                <label class="form-label">Eligibility</label>
                <textarea name="eligibility" class="form-control" rows="3"><?= htmlspecialchars($ev['eligibility'] ?? '') ?></textarea>
              </div>
              <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary btn-lg">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary btn-lg">Cancel</a>
              </div>
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
