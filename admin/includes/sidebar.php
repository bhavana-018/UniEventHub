<?php
// admin/includes/sidebar.php
// Requires: $user, $unreadNotif, $unreadMsg, $pendingEvts, $activePage
// activePage: 'dashboard'|'events'|'users'|'reports'|'messages'|'notifications'
$initials = strtoupper(substr($user['full_name'], 0, 1));
?>
<aside class="sidebar" id="sidebar">

  <!-- Brand -->
  <div class="sidebar-brand">
    <div class="sidebar-logo-mark">🎓</div>
    <div class="sidebar-brand-info">
      <div class="sidebar-brand-name">UniEventHub</div>
      <div class="sidebar-brand-sub">Admin Panel</div>
    </div>
  </div>

  <!-- Nav -->
  <nav class="sidebar-nav">
    <div class="sidebar-section">
      <div class="sidebar-section-label">Management</div>

      <a href="dashboard.php" class="sidebar-link <?= $activePage==='dashboard'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>
        Dashboard
      </a>

      <a href="events.php" class="sidebar-link <?= $activePage==='events'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        Manage Events
        <?php if (isset($pendingEvts) && $pendingEvts > 0): ?>
          <span style="margin-left:auto;background:var(--warning);color:#111;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:var(--r-full);min-width:18px;text-align:center"><?= $pendingEvts ?></span>
        <?php endif; ?>
      </a>

      <a href="users.php" class="sidebar-link <?= $activePage==='users'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        Manage Users
      </a>

      <a href="reports.php" class="sidebar-link <?= $activePage==='reports'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
        Reports
      </a>

      <a href="messages.php" class="sidebar-link <?= $activePage==='messages'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        Messages
        <?php if ($unreadMsg > 0): ?>
          <span style="margin-left:auto;background:var(--danger);color:#fff;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:var(--r-full);min-width:18px;text-align:center"><?= $unreadMsg ?></span>
        <?php endif; ?>
      </a>

      <a href="notifications.php" class="sidebar-link <?= $activePage==='notifications'?'active':'' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
        Notifications
        <?php if ($unreadNotif > 0): ?>
          <span style="margin-left:auto;background:var(--danger);color:#fff;font-size:.62rem;font-weight:700;padding:2px 7px;border-radius:var(--r-full);min-width:18px;text-align:center"><?= $unreadNotif ?></span>
        <?php endif; ?>
      </a>
    </div>
  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <div class="sidebar-user">
      <div class="sidebar-avatar" style="background:linear-gradient(135deg,var(--danger),#9333EA)"><?= $initials ?></div>
      <div class="sidebar-user-info">
        <div class="sidebar-user-name"><?= htmlspecialchars($user['full_name']) ?></div>
        <div class="sidebar-user-role">Administrator</div>
      </div>
    </div>
    <a href="../php/logout.php" class="sidebar-link" style="color:var(--text-4)">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Logout
    </a>
  </div>
</aside>
