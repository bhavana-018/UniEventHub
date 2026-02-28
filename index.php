<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';

$stats['events']   = $pdo->query("SELECT COUNT(*) FROM events WHERE status='approved'")->fetchColumn();
$stats['students'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
$stats['regs']     = $pdo->query("SELECT COUNT(*) FROM registrations")->fetchColumn();
$stats['cats']     = 6;

$stmt = $pdo->query("SELECT e.*, u.full_name AS organizer_name,
    (SELECT COUNT(*) FROM registrations r WHERE r.event_id = e.id) AS reg_count
    FROM events e JOIN users u ON e.organizer_id = u.id
    WHERE e.status = 'approved' AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC LIMIT 6");
$events = $stmt->fetchAll();
$flash  = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>UniEventHub — University Event Management</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>
<body>

<!-- Top glow accent line -->
<div class="page-glow-line"></div>

<!-- ── Navbar ──────────────────────────────────────────────── -->
<nav class="navbar">
  <div class="nav-inner">
    <a href="index.php" class="nav-brand" style="text-decoration:none">
      <div class="logo-mark">🎓</div>
      <span class="logo-text">UniEventHub</span>
    </a>
    <div class="nav-links">
      <a href="index.php" class="active">Home</a>
      <a href="events.php">Events</a>
      <?php if (isLoggedIn()): ?>
        <?php if ($_SESSION['user_role']==='admin'):     ?><a href="admin/dashboard.php">Admin Panel</a><?php endif; ?>
        <?php if ($_SESSION['user_role']==='organizer'): ?><a href="organizer/dashboard.php">Dashboard</a><?php endif; ?>
        <?php if ($_SESSION['user_role']==='student'):   ?><a href="student/dashboard.php">Dashboard</a><?php endif; ?>
      <?php endif; ?>
    </div>
    <div class="nav-actions">
      <?php if (!isLoggedIn()): ?>
        <a href="login.php"    class="btn btn-ghost btn-sm">Sign In</a>
        <a href="register.php" class="btn btn-primary btn-sm">Get Started</a>
      <?php else: ?>
        <a href="<?= $_SESSION['user_role'] ?>/dashboard.php" class="btn btn-ghost btn-sm">Dashboard</a>
        <a href="php/logout.php" class="btn btn-danger btn-sm">Log Out</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php if ($flash): ?>
<div class="container" style="padding-top:18px">
  <div class="alert alert-<?= $flash['type']==='error'?'danger':$flash['type'] ?> alert-auto-dismiss">
    <?= htmlspecialchars($flash['message']) ?>
  </div>
</div>
<?php endif; ?>

<!-- ── Hero ─────────────────────────────────────────────────── -->
<section class="hero">
  <div class="hero-content">
    <div class="hero-eyebrow">
      <span class="dot"></span>
      University Event Platform
    </div>
    <h1>
      Discover &amp; Join<br>
      <span class="gradient-text">Campus Events</span>
    </h1>
    <p>Seminars, workshops, cultural fests, sports meets and more — all organized in one beautifully centralized platform. Register in seconds.</p>
    <div class="hero-actions">
      <a href="events.php" class="btn-hero-primary">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Browse Events
      </a>
      <?php if (!isLoggedIn()): ?>
      <a href="register.php" class="btn-hero-secondary">
        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
        Create Free Account
      </a>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── Stats Card ────────────────────────────────────────────── -->
<div class="container">
  <div class="card" style="margin-top:-38px;position:relative;z-index:10;border-color:var(--border-2)">
    <div class="stats-bar">
      <div class="stat-item">
        <div class="stat-number"><?= $stats['events'] ?>+</div>
        <div class="stat-label">Active Events</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= $stats['students'] ?>+</div>
        <div class="stat-label">Registered Students</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= $stats['regs'] ?>+</div>
        <div class="stat-label">Event Registrations</div>
      </div>
      <div class="stat-item">
        <div class="stat-number"><?= $stats['cats'] ?></div>
        <div class="stat-label">Event Categories</div>
      </div>
    </div>
  </div>
</div>

<!-- ── Upcoming Events ───────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Upcoming</div>
      <h2 class="section-title">Events Happening Soon</h2>
      <p class="section-subtitle">Don't miss these exciting upcoming experiences across campus</p>
    </div>

    <?php if (empty($events)): ?>
    <div class="empty-state">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      <h3>No upcoming events yet</h3>
      <p>Check back soon for new events!</p>
    </div>
    <?php else: ?>

    <div class="event-grid">
      <?php
      $catEmoji = ['seminar'=>'📚','workshop'=>'🔧','cultural'=>'🎭','sports'=>'⚽','technical'=>'💻','guest_lecture'=>'🎤','other'=>'🎪'];
      $catColors = ['seminar'=>'badge-info','workshop'=>'badge-warning','cultural'=>'badge-cyan','sports'=>'badge-success','technical'=>'badge-primary','guest_lecture'=>'badge-violet','other'=>'badge-gray'];
      foreach ($events as $ev):
        $pct = $ev['max_participants'] > 0 ? ($ev['reg_count'] / $ev['max_participants']) * 100 : 0;
      ?>
      <div class="event-card">
        <div class="event-card-img">
          <?php if ($ev['poster_url']): ?>
            <img src="<?= htmlspecialchars($ev['poster_url']) ?>" alt="">
          <?php else: ?>
            <span style="position:relative;z-index:1;font-size:3rem;filter:drop-shadow(0 2px 12px rgba(0,0,0,.4))"><?= $catEmoji[$ev['category']] ?? '🎪' ?></span>
          <?php endif; ?>
        </div>
        <div class="event-card-body">
          <div style="margin-bottom:9px">
            <span class="badge <?= $catColors[$ev['category']] ?? 'badge-gray' ?> badge-nodot">
              <?= ucfirst(str_replace('_',' ',$ev['category'])) ?>
            </span>
          </div>
          <div class="event-card-title"><?= htmlspecialchars($ev['title']) ?></div>
          <div class="event-meta">
            <div class="event-meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
              <?= date('M j, Y', strtotime($ev['event_date'])) ?> &middot; <?= date('g:i A', strtotime($ev['start_time'])) ?>
            </div>
            <div class="event-meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <?= htmlspecialchars($ev['venue'] ?: 'Venue TBA') ?>
            </div>
            <div class="event-meta-item">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
              <?= $ev['reg_count'] ?> / <?= $ev['max_participants'] ?> registered
            </div>
          </div>
          <!-- Capacity bar -->
          <div class="progress-bar" style="margin-top:4px;margin-bottom:0">
            <div class="progress-fill" style="width:<?= min(100,$pct) ?>%;background:<?= $pct >= 90 ? 'var(--danger)' : ($pct >= 60 ? 'var(--warning)' : '') ?>"></div>
          </div>
        </div>
        <div class="event-card-footer">
          <span style="font-weight:700;font-size:.9rem;<?= $ev['registration_fee']>0 ? 'color:var(--indigo-light)' : 'color:var(--success)' ?>">
            <?= $ev['registration_fee'] > 0 ? '₹'.number_format($ev['registration_fee'],2) : '✓ Free' ?>
          </span>
          <a href="event-detail.php?id=<?= $ev['id'] ?>" class="btn btn-primary btn-sm">View Details →</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center" style="margin-top:42px">
      <a href="events.php" class="btn btn-ghost" style="padding:11px 30px">
        View All Events
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ── Features Section ─────────────────────────────────────── -->
<section class="section" style="background:var(--bg-1);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:72px 24px">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Platform</div>
      <h2 class="section-title">Why UniEventHub?</h2>
      <p class="section-subtitle">Everything you need to discover, join and manage campus events</p>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:18px">
      <?php
      $features = [
        ['📅','icon-purple','Easy Registration','Register for events in seconds with just a few clicks.','Click any event → hit Register → done.'],
        ['🔔','icon-cyan','Real-time Alerts','Stay updated with event reminders and instant notifications.','Never miss deadlines or schedule changes.'],
        ['📊','icon-green','Track Everything','View history, upcoming events, and attendance records.','Your personal event dashboard at a glance.'],
        ['🔒','icon-yellow','Secure & Private','Your data is protected with industry-standard security.','Role-based access for students, organizers and admins.'],
      ];
      foreach ($features as $f): ?>
      <div class="card" style="padding:24px;transition:var(--t-lg)" onmouseenter="this.style.borderColor='var(--indigo-border)'" onmouseleave="this.style.borderColor='var(--border)'">
        <div class="stat-card-icon <?= $f[1] ?>" style="margin-bottom:16px;width:48px;height:48px">
          <span style="font-size:1.5rem"><?= $f[0] ?></span>
        </div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-1);margin-bottom:6px"><?= $f[2] ?></div>
        <div style="font-size:.8rem;color:var(--text-4);line-height:1.65;margin-bottom:8px"><?= $f[3] ?></div>
        <div style="font-size:.74rem;color:var(--text-5)"><?= $f[4] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── Roles Section ─────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Who It's For</div>
      <h2 class="section-title">Built for everyone on campus</h2>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px">
      <?php
      $roles = [
        ['🎓','Students','Browse events, register instantly, track attendance, get notified.','student'],
        ['🗂️','Organizers','Create events, manage registrations, track participants.','organizer'],
        ['🔐','Administrators','Approve events, manage users, view reports and analytics.','admin'],
      ];
      foreach ($roles as $r): ?>
      <div class="card card-glass" style="padding:28px;text-align:center">
        <div style="font-size:2.5rem;margin-bottom:14px"><?= $r[0] ?></div>
        <div style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1rem;font-weight:700;color:var(--text-1);margin-bottom:8px"><?= $r[1] ?></div>
        <div style="font-size:.82rem;color:var(--text-4);line-height:1.65"><?= $r[2] ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────────── -->
<?php if (!isLoggedIn()): ?>
<section class="section" style="padding:80px 24px;background:var(--bg-1);border-top:1px solid var(--border)">
  <div class="container">
    <div style="
      max-width:700px; margin:0 auto; text-align:center;
      background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(6,182,212,.08));
      border:1px solid rgba(99,102,241,.25);
      border-radius:var(--r-xl); padding:60px 40px;
      position:relative; overflow:hidden;
    ">
      <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(99,102,241,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(99,102,241,.04) 1px,transparent 1px);background-size:36px 36px"></div>
      <div style="position:relative;z-index:1">
        <div class="section-tag">Get Started Free</div>
        <h2 class="section-title" style="margin-bottom:14px;margin-top:8px">Ready to join the community?</h2>
        <p style="color:var(--text-4);margin-bottom:32px;font-size:.9rem">Create your free account and start discovering events happening around your campus. No credit card required.</p>
        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
          <a href="register.php" class="btn btn-primary btn-lg">
            Create Free Account
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </a>
          <a href="login.php" class="btn btn-ghost btn-lg">Sign In</a>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ── Footer ────────────────────────────────────────────────── -->
<footer class="footer">
  <div class="footer-inner">
    <div class="footer-top">
      <div>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
          <div class="logo-mark" style="width:28px;height:28px;font-size:13px;border-radius:8px">🎓</div>
          <div class="footer-brand-name" style="margin-bottom:0">UniEventHub</div>
        </div>
        <div class="footer-desc">A centralized platform for managing and participating in university events. Making campus life more organized and engaging.</div>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Platform</div>
        <a href="index.php">Home</a>
        <a href="events.php">Browse Events</a>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Event Types</div>
        <a href="events.php?cat=seminar">Seminars</a>
        <a href="events.php?cat=workshop">Workshops</a>
        <a href="events.php?cat=cultural">Cultural</a>
        <a href="events.php?cat=sports">Sports</a>
      </div>
      <div class="footer-col">
        <div class="footer-col-title">Portals</div>
        <a href="student/dashboard.php">Student Portal</a>
        <a href="organizer/dashboard.php">Organizer Portal</a>
        <a href="admin/dashboard.php">Admin Panel</a>
      </div>
    </div>
    <div class="footer-bottom">&copy; <?= date('Y') ?> UniEventHub. All rights reserved.</div>
  </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>
