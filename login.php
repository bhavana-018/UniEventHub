<?php
require_once 'includes/auth.php';
if (isLoggedIn()) redirect($_SESSION['user_role'].'/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — UniEventHub</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🎓</text></svg>">
</head>
<body>

<div class="auth-split">

  <!-- ── Left Panel: Branding ───────────────────────────────── -->
  <div class="auth-panel-left">
    <div class="auth-panel-content">

      <div class="auth-panel-brand">
        <div class="auth-panel-logo">🎓</div>
        <div class="auth-panel-brand-name">UniEventHub</div>
      </div>

      <h1 class="auth-panel-headline">
        Your campus,<br>
        <span class="hl">all in one place.</span>
      </h1>
      <p class="auth-panel-sub">
        Discover events, register instantly, track your participation,
        and stay notified — built for students and organizers alike.
      </p>

      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon" style="background:rgba(99,102,241,.15)">📅</div>
          <div class="auth-feature-text">Browse & register for campus events in seconds</div>
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon" style="background:rgba(6,182,212,.15)">🔔</div>
          <div class="auth-feature-text">Real-time notifications and event reminders</div>
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon" style="background:rgba(16,185,129,.15)">📊</div>
          <div class="auth-feature-text">Track your attendance and event history</div>
        </div>
      </div>

    </div>
  </div>

  <!-- ── Right Panel: Form ──────────────────────────────────── -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap">

      <div class="auth-form-header">
        <div class="auth-form-title">Welcome back</div>
        <div class="auth-form-sub">Sign in to your account to continue</div>
      </div>

      <div id="form-alert" style="display:none;margin-bottom:16px"></div>

      <form id="login-form" novalidate>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-icon-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" name="email" id="email" class="form-control"
              placeholder="you@university.edu" required autofocus>
          </div>
          <div class="form-error" data-error="email"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-icon-wrap" style="position:relative">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <input type="password" name="password" id="password" class="form-control"
              placeholder="Your password" required>
            <button type="button" id="toggle-pass"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-4);display:flex">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
          </div>
          <div class="form-error" data-error="password"></div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submit-btn" style="margin-top:6px">
          <span id="btn-text">Sign In</span>
          <span id="btn-spinner" class="spinner hidden"></span>
        </button>
      </form>

      <div class="auth-form-footer">
        Don't have an account? <a href="register.php">Create one free →</a>
      </div>

      <!-- Back to home link -->
      <div style="text-align:center;margin-top:24px">
        <a href="index.php" style="font-size:.78rem;color:var(--text-5);display:inline-flex;align-items:center;gap:5px">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
          Back to homepage
        </a>
      </div>

    </div>
  </div>
</div>

<script src="js/main.js"></script>
<script>
togglePassword('password','toggle-pass');

document.getElementById('login-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const valid = validateForm('login-form', {
    email:    { required: 'Email is required' },
    password: { required: 'Password is required' }
  });
  if (!valid) return;

  const btn = document.getElementById('submit-btn');
  const btnText = document.getElementById('btn-text');
  const spinner = document.getElementById('btn-spinner');
  btn.disabled = true;
  btnText.textContent = 'Signing in…';
  spinner.classList.remove('hidden');

  try {
    const res  = await fetch('php/login.php', { method:'POST', body: new FormData(this) });
    const data = await res.json();
    const alertEl = document.getElementById('form-alert');

    if (data.success) {
      alertEl.className = 'alert alert-success';
      alertEl.innerHTML = '✓ Login successful! Redirecting…';
      alertEl.style.display = 'flex';
      setTimeout(() => window.location.href = data.redirect, 800);
    } else {
      alertEl.className = 'alert alert-danger';
      alertEl.innerHTML = '✕ ' + data.message;
      alertEl.style.display = 'flex';
      btn.disabled = false;
      btnText.textContent = 'Sign In';
      spinner.classList.add('hidden');
    }
  } catch {
    showToast('Server error. Please try again.', 'error');
    btn.disabled = false;
    btnText.textContent = 'Sign In';
    spinner.classList.add('hidden');
  }
});
</script>
</body>
</html>
