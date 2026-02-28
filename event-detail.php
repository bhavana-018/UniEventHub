<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: events.php'); exit; }

$stmt = $pdo->prepare("SELECT e.*, u.full_name AS organizer_name, u.department AS organizer_dept,
    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
    FROM events e JOIN users u ON e.organizer_id = u.id
    WHERE e.id = ? AND e.status = 'approved'");
$stmt->execute([$id]);
$ev = $stmt->fetch();

if (!$ev) { header('Location: events.php'); exit; }

$isRegistered = false;
if (isLoggedIn() && $_SESSION['user_role'] === 'student') {
    $check = $pdo->prepare("SELECT id FROM registrations WHERE event_id = ? AND student_id = ?");
    $check->execute([$id, $_SESSION['user_id']]);
    $isRegistered = (bool)$check->fetch();
}

$isFull = $ev['reg_count'] >= $ev['max_participants'];
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($ev['title']) ?> – UniEventHub</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-brand"><span>UniEventHub</span></a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="events.php">Events</a>
    </div>
    <div class="nav-actions">
      <?php if (!isLoggedIn()): ?>
        <a href="login.php" class="btn btn-secondary btn-sm">Log In</a>
        <a href="register.php" class="btn btn-primary btn-sm">Sign Up</a>
      <?php else: ?>
        <a href="<?= $_SESSION['user_role'] ?>/dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
        <a href="php/logout.php" class="btn btn-primary btn-sm">Logout</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="container" style="padding: 32px 24px">
  <?php if ($flash): ?>
  <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss mb-3">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <!-- Event Header -->
    <div class="event-detail-header">
      <div class="detail-badge-row">
        <span class="badge" style="background:rgba(255,255,255,.2);color:white"><?= ucfirst(str_replace('_',' ',$ev['category'])) ?></span>
        <?php if ($isFull): ?><span class="badge" style="background:rgba(239,68,68,.3);color:white">Full</span><?php endif; ?>
      </div>
      <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;margin-bottom:8px"><?= htmlspecialchars($ev['title']) ?></h1>
      <div style="opacity:.85">Organized by <?= htmlspecialchars($ev['organizer_name']) ?> &bull; <?= htmlspecialchars($ev['organizer_dept'] ?? '') ?></div>
    </div>

    <div class="card-body">
      <!-- Meta Grid -->
      <div class="detail-meta-grid">
        <div class="detail-meta-item">
          <div class="detail-meta-label">📅 Date</div>
          <div class="detail-meta-value"><?= date('l, M j, Y', strtotime($ev['event_date'])) ?></div>
        </div>
        <div class="detail-meta-item">
          <div class="detail-meta-label">⏰ Time</div>
          <div class="detail-meta-value"><?= date('g:i A', strtotime($ev['start_time'])) ?><?= $ev['end_time'] ? ' – '.date('g:i A', strtotime($ev['end_time'])) : '' ?></div>
        </div>
        <div class="detail-meta-item">
          <div class="detail-meta-label">📍 Venue</div>
          <div class="detail-meta-value"><?= htmlspecialchars($ev['venue'] ?: 'TBA') ?></div>
        </div>
        <div class="detail-meta-item">
          <div class="detail-meta-label">👥 Capacity</div>
          <div class="detail-meta-value"><?= $ev['reg_count'] ?> / <?= $ev['max_participants'] ?> seats filled</div>
        </div>
        <div class="detail-meta-item">
          <div class="detail-meta-label">💰 Fee</div>
          <div class="detail-meta-value"><?= $ev['registration_fee'] > 0 ? '₹'.number_format($ev['registration_fee'],2) : 'Free' ?></div>
        </div>
      </div>

      <!-- Description -->
      <div style="margin-bottom:24px">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:10px;color:var(--gray-900)">About this Event</h3>
        <p style="color:var(--gray-600);line-height:1.8"><?= nl2br(htmlspecialchars($ev['description'] ?: 'No description provided.')) ?></p>
      </div>

      <?php if ($ev['eligibility']): ?>
      <div style="margin-bottom:24px">
        <h3 style="font-size:1rem;font-weight:700;margin-bottom:10px;color:var(--gray-900)">Eligibility</h3>
        <p style="color:var(--gray-600)"><?= nl2br(htmlspecialchars($ev['eligibility'])) ?></p>
      </div>
      <?php endif; ?>

      <!-- Registration Action -->
      <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;padding-top:20px;border-top:1px solid var(--gray-100)">
        <?php if (!isLoggedIn()): ?>
          <a href="login.php" class="btn btn-primary btn-lg">Login to Register</a>
        <?php elseif ($_SESSION['user_role'] !== 'student'): ?>
          <div class="alert alert-info" style="margin:0;padding:12px 16px">Registration is only for students.</div>
        <?php elseif ($isRegistered): ?>
          <button class="btn btn-success btn-lg" disabled>✅ Already Registered</button>
          <a href="php/cancel_registration.php?event_id=<?= $ev['id'] ?>" class="btn btn-secondary" onclick="return confirm('Cancel your registration?')">Cancel Registration</a>
        <?php elseif ($isFull): ?>
          <button class="btn btn-lg" disabled style="background:var(--gray-200);color:var(--gray-500)">Event is Full</button>
        <?php elseif ($ev['event_date'] < date('Y-m-d')): ?>
          <button class="btn btn-lg" disabled style="background:var(--gray-200);color:var(--gray-500)">Event has passed</button>
        <?php else: ?>
          <button onclick="openModal('register-modal')" class="btn btn-primary btn-lg">Register Now</button>
        <?php endif; ?>
        <a href="events.php" class="btn btn-secondary">← Back to Events</a>
      </div>
    </div>
  </div>
</div>

<!-- Registration Modal -->
<?php if (isLoggedIn() && $_SESSION['user_role'] === 'student' && !$isRegistered && !$isFull && $ev['event_date'] >= date('Y-m-d')): ?>
<div class="modal-overlay" id="register-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">Confirm Registration</div>
      <button class="modal-close" data-modal-close="register-modal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <p style="color:var(--gray-600);margin-bottom:16px">You are about to register for:</p>
      <div style="background:var(--gray-50);padding:16px;border-radius:var(--radius);margin-bottom:20px;border:1px solid var(--gray-200)">
        <div style="font-weight:700;color:var(--gray-900)"><?= htmlspecialchars($ev['title']) ?></div>
        <div style="font-size:.875rem;color:var(--gray-500);margin-top:4px"><?= date('M j, Y', strtotime($ev['event_date'])) ?> • <?= htmlspecialchars($ev['venue'] ?: 'TBA') ?></div>
        <div style="font-size:.875rem;margin-top:4px;font-weight:600;color:var(--primary)"><?= $ev['registration_fee'] > 0 ? 'Fee: ₹'.number_format($ev['registration_fee'],2) : 'Free Event' ?></div>
      </div>
      <div id="reg-alert" style="display:none"></div>
      <div style="display:flex;gap:10px">
        <button onclick="submitRegistration(<?= $ev['id'] ?>)" class="btn btn-primary" style="flex:1" id="confirm-reg-btn">Confirm Registration</button>
        <button class="btn btn-secondary" data-modal-close="register-modal">Cancel</button>
      </div>
    </div>
  </div>
</div>
<script>
async function submitRegistration(eventId) {
  const btn = document.getElementById('confirm-reg-btn');
  btn.disabled = true; btn.textContent = 'Registering...';
  try {
    const res  = await fetch('php/register_event.php', { method:'POST', body: new URLSearchParams({ event_id: eventId }) });
    const data = await res.json();
    const alert = document.getElementById('reg-alert');
    if (data.success) {
      alert.className = 'alert alert-success'; alert.innerHTML = '✅ ' + data.message; alert.style.display='flex';
      setTimeout(() => location.reload(), 1500);
    } else {
      alert.className = 'alert alert-danger'; alert.innerHTML = '❌ ' + data.message; alert.style.display='flex';
      btn.disabled = false; btn.textContent = 'Confirm Registration';
    }
  } catch { showToast('Error. Please try again.','error'); btn.disabled=false; btn.textContent='Confirm Registration'; }
}
</script>
<?php endif; ?>

<footer class="footer"><div class="footer-inner"><div class="footer-bottom">&copy; <?= date('Y') ?> UniEventHub. All rights reserved.</div></div></footer>
<script src="js/main.js"></script>
</body>
</html>
