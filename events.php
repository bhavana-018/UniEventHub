<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$cat = $_GET['cat'] ?? '';
$allowed = ['seminar','workshop','cultural','sports','technical','guest_lecture','other'];
$catFilter = in_array($cat, $allowed) ? $cat : '';

$sql = "SELECT e.*, u.full_name AS organizer_name,
    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
    FROM events e
    JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'approved'";
$params = [];
if ($catFilter) { $sql .= " AND e.category = ?"; $params[] = $catFilter; }
$sql .= " ORDER BY e.event_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$events = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Events – UniEventHub</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-brand"><span>UniEventHub</span></a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="events.php" class="active">Events</a>
      <?php if (isLoggedIn()): ?>
        <a href="<?= $_SESSION['user_role'] ?>/dashboard.php">Dashboard</a>
      <?php endif; ?>
    </div>
    <div class="nav-actions">
      <?php if (!isLoggedIn()): ?>
        <a href="login.php" class="btn btn-secondary btn-sm">Log In</a>
        <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
      <?php else: ?>
        <a href="php/logout.php" class="btn btn-secondary btn-sm">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<section class="section">
  <div class="container">
    <div class="section-header" style="text-align:left;margin-bottom:28px">
      <h1 class="section-title">All Events</h1>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
      <div class="search-input-wrap">
        <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="event-search" class="form-control" placeholder="Search events...">
      </div>
      <select id="filter-category" class="form-control" style="max-width:180px">
        <option value="">All Categories</option>
        <option value="seminar"      <?= $catFilter==='seminar'?'selected':'' ?>>Seminar</option>
        <option value="workshop"     <?= $catFilter==='workshop'?'selected':'' ?>>Workshop</option>
        <option value="cultural"     <?= $catFilter==='cultural'?'selected':'' ?>>Cultural</option>
        <option value="sports"       <?= $catFilter==='sports'?'selected':'' ?>>Sports</option>
        <option value="technical"    <?= $catFilter==='technical'?'selected':'' ?>>Technical</option>
        <option value="guest_lecture"<?= $catFilter==='guest_lecture'?'selected':'' ?>>Guest Lecture</option>
        <option value="other"        <?= $catFilter==='other'?'selected':'' ?>>Other</option>
      </select>
    </div>

    <div class="event-grid" id="events-container">
      <?php foreach ($events as $ev): ?>
      <div class="event-card-wrap" data-title="<?= htmlspecialchars(strtolower($ev['title'])) ?>" data-category="<?= $ev['category'] ?>">
        <div class="event-card">
          <div class="event-card-img">
            <?php if ($ev['poster_url']): ?>
              <img src="<?= htmlspecialchars($ev['poster_url']) ?>" alt="">
            <?php else: ?>
              <span style="font-size:3rem"><?= ['seminar'=>'📚','workshop'=>'🔧','cultural'=>'🎭','sports'=>'⚽','technical'=>'💻','guest_lecture'=>'🎤','other'=>'🎪'][$ev['category']] ?? '🎪' ?></span>
            <?php endif; ?>
          </div>
          <div class="event-card-body">
            <div class="mb-2">
              <span class="badge badge-primary"><?= ucfirst(str_replace('_',' ',$ev['category'])) ?></span>
              <?php if ($ev['event_date'] < date('Y-m-d')): ?><span class="badge badge-gray">Past</span><?php endif; ?>
            </div>
            <div class="event-card-title"><?= htmlspecialchars($ev['title']) ?></div>
            <div class="event-meta">
              <div class="event-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <?= date('M j, Y', strtotime($ev['event_date'])) ?>
              </div>
              <div class="event-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <?= htmlspecialchars($ev['venue'] ?: 'TBA') ?>
              </div>
              <div class="event-meta-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                <?= $ev['reg_count'] ?> / <?= $ev['max_participants'] ?> registered
              </div>
            </div>
          </div>
          <div class="event-card-footer">
            <span class="fw-600 text-primary"><?= $ev['registration_fee'] > 0 ? '₹'.number_format($ev['registration_fee'],2) : 'Free' ?></span>
            <a href="event-detail.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">View Details</a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div id="events-empty" class="empty-state" style="display:none">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <h3>No events found</h3>
      <p>Try adjusting your search or category filter</p>
    </div>

    <?php if (empty($events)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <h3>No events available</h3>
      <p>Check back soon for upcoming events</p>
    </div>
    <?php endif; ?>
  </div>
</section>

<footer class="footer"><div class="footer-inner"><div class="footer-bottom">&copy; <?= date('Y') ?> UniEventHub. All rights reserved.</div></div></footer>

<script src="js/main.js"></script>
<script>initEventSearch();</script>
</body>
</html>
