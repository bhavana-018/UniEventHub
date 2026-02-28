<?php
require_once 'includes/auth.php';
if (isLoggedIn()) redirect($_SESSION['user_role'].'/dashboard.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create Account — UniEventHub</title>
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
        Join your<br>
        <span class="hl">campus community.</span>
      </h1>
      <p class="auth-panel-sub">
        Whether you're a student looking to participate or an organizer
        managing events — UniEventHub has everything you need.
      </p>

      <div class="auth-features">
        <div class="auth-feature">
          <div class="auth-feature-icon" style="background:rgba(245,158,11,.15)">🎟️</div>
          <div class="auth-feature-text">Register for seminars, workshops, sports & more</div>
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon" style="background:rgba(139,92,246,.15)">🗂️</div>
          <div class="auth-feature-text">Organizers can create and manage events with ease</div>
        </div>
        <div class="auth-feature">
          <div class="auth-feature-icon" style="background:rgba(99,102,241,.15)">💬</div>
          <div class="auth-feature-text">Stay connected with built-in messaging & notifications</div>
        </div>
      </div>

    </div>
  </div>

  <!-- ── Right Panel: Form ──────────────────────────────────── -->
  <div class="auth-panel-right">
    <div class="auth-form-wrap" style="max-width:460px">

      <div class="auth-form-header">
        <div class="auth-form-title">Create your account</div>
        <div class="auth-form-sub">Join the campus event community today</div>
      </div>

      <!-- Role Selector -->
      <div class="role-selector" id="role-selector">
        <label class="role-btn selected">
          <input type="radio" name="role_ui" value="student" checked>
          <div class="role-icon">🎓</div>
          <div class="role-name">Student</div>
        </label>
        <label class="role-btn">
          <input type="radio" name="role_ui" value="organizer">
          <div class="role-icon">🗂️</div>
          <div class="role-name">Organizer</div>
        </label>
      </div>

      <div id="form-alert" style="display:none;margin-bottom:16px"></div>

      <form id="register-form" novalidate>
        <input type="hidden" name="role" id="role-hidden" value="student">

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <div class="input-icon-wrap">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input type="text" name="full_name" id="full_name" class="form-control"
                placeholder="Your full name" required>
            </div>
            <div class="form-error" data-error="full_name"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Phone Number</label>
            <input type="tel" name="phone" class="form-control" placeholder="+91 98765 43210">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-icon-wrap">
            <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            <input type="email" name="email" id="email" class="form-control"
              placeholder="you@university.edu" required>
          </div>
          <div class="form-error" data-error="email"></div>
        </div>

        <div class="form-group">
          <label class="form-label">Department</label>
          <select name="department" class="form-control">
            <option value="">Select your department</option>
            <option>Computer Science</option>
            <option>Information Technology</option>
            <option>Electronics &amp; Communication</option>
            <option>Mechanical Engineering</option>
            <option>Civil Engineering</option>
            <option>Business Administration</option>
            <option>Arts &amp; Humanities</option>
            <option>Science</option>
            <option>Other</option>
          </select>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-icon-wrap" style="position:relative">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="password" id="password" class="form-control"
                placeholder="Min. 8 characters" required>
              <button type="button" id="toggle-pass"
                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-4);display:flex">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </button>
            </div>
            <div class="password-strength" id="strength-bars">
              <div class="strength-bar"></div><div class="strength-bar"></div>
              <div class="strength-bar"></div><div class="strength-bar"></div>
            </div>
            <div class="form-error" data-error="password"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <div class="input-icon-wrap">
              <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <input type="password" name="confirm_password" id="confirm_password"
                class="form-control" placeholder="Re-enter password" required>
            </div>
            <div class="form-error" data-error="confirm_password"></div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="submit-btn" style="margin-top:4px">
          <span id="btn-text">Create Account</span>
          <span id="btn-spinner" class="spinner hidden"></span>
        </button>
      </form>

      <div class="auth-form-footer">
        Already have an account? <a href="login.php">Sign in →</a>
      </div>

      <div style="text-align:center;margin-top:20px">
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
initPasswordStrength('password','strength-bars');
togglePassword('password','toggle-pass');
initRoleSelector();

document.querySelectorAll('.role-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const val = btn.querySelector('input').value;
    document.getElementById('role-hidden').value = val;
  });
});

document.getElementById('register-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const valid = validateForm('register-form', {
    full_name: { required: 'Full name is required' },
    email:     { required: 'Email is required', pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/, pattern_msg: 'Enter a valid email' },
    password:  { required: 'Password is required', minLength: 8, minLength_msg: 'Minimum 8 characters' },
    confirm_password: { required: 'Please confirm your password', match: 'password', match_msg: 'Passwords do not match' }
  });
  if (!valid) return;

  const btn = document.getElementById('submit-btn');
  const btnText = document.getElementById('btn-text');
  const spinner = document.getElementById('btn-spinner');
  btn.disabled = true; btnText.textContent = 'Creating…'; spinner.classList.remove('hidden');

  try {
    const res  = await fetch('php/register.php', { method:'POST', body: new FormData(this) });
    const data = await res.json();
    const alertEl = document.getElementById('form-alert');
    if (data.success) {
      alertEl.className = 'alert alert-success';
      alertEl.innerHTML = '✓ ' + data.message + ' Redirecting…';
      alertEl.style.display = 'flex';
      setTimeout(() => window.location.href = data.redirect || 'login.php', 1400);
    } else {
      alertEl.className = 'alert alert-danger';
      alertEl.innerHTML = '✕ ' + data.message;
      alertEl.style.display = 'flex';
      btn.disabled = false; btnText.textContent = 'Create Account'; spinner.classList.add('hidden');
    }
  } catch {
    showToast('Server error. Please try again.','error');
    btn.disabled = false; btnText.textContent = 'Create Account'; spinner.classList.add('hidden');
  }
});
</script>
</body>
</html>
