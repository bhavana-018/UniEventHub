// js/main.js — UniEventHub v2

/* ── Toast Notifications ─────────────────────────────────── */
function showToast(message, type = 'info') {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const icons = {
    success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="9 12 11 14 15 10"/></svg>',
    error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
    info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
  };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `${icons[type]||icons.info}<span class="toast-msg">${escapeHtml(message)}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.animation = 'toastIn .3s ease reverse';
    setTimeout(() => toast.remove(), 320);
  }, 3500);
}

/* ── Password Strength ───────────────────────────────────── */
function initPasswordStrength(inputId, barsId) {
  const input = document.getElementById(inputId);
  const bars  = document.querySelectorAll(`#${barsId} .strength-bar`);
  if (!input || !bars.length) return;
  input.addEventListener('input', () => {
    const v = input.value;
    let score = 0;
    if (v.length >= 8)           score++;
    if (/[A-Z]/.test(v))         score++;
    if (/[0-9]/.test(v))         score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;
    bars.forEach((b, i) => {
      b.className = 'strength-bar';
      if (i < score) {
        b.classList.add('active');
        b.classList.add(score <= 1 ? 'weak' : score <= 2 ? 'medium' : 'strong');
      }
    });
  });
}

/* ── Toggle Password Visibility ─────────────────────────── */
function togglePassword(inputId, btnId) {
  const input = document.getElementById(inputId);
  const btn   = document.getElementById(btnId);
  if (!input || !btn) return;
  btn.addEventListener('click', () => {
    input.type = input.type === 'password' ? 'text' : 'password';
    btn.style.opacity = input.type === 'text' ? '.5' : '1';
  });
}

/* ── Modal Helpers ───────────────────────────────────────── */
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}
function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) e.target.classList.remove('open');
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  }
});

/* ── Sidebar Toggle ──────────────────────────────────────── */
function initSidebar() {
  const toggleBtn = document.getElementById('sidebar-toggle');
  const sidebar   = document.getElementById('sidebar');
  if (!toggleBtn || !sidebar) return;
  toggleBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    sidebar.classList.toggle('open');
  });
  document.addEventListener('click', (e) => {
    if (window.innerWidth < 769 &&
        !sidebar.contains(e.target) &&
        !toggleBtn.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });
}

/* ── Form Validation ─────────────────────────────────────── */
function validateForm(formId, rules) {
  const form = document.getElementById(formId);
  if (!form) return false;
  let valid = true;
  for (const [name, rule] of Object.entries(rules)) {
    const field = form.querySelector(`[name="${name}"]`);
    const errEl = form.querySelector(`[data-error="${name}"]`);
    if (!field) continue;
    let msg = '';
    const val = field.value.trim();
    if (rule.required && !val)                                msg = rule.required;
    else if (rule.minLength && val.length < rule.minLength)   msg = rule.minLength_msg || `Min ${rule.minLength} chars`;
    else if (rule.pattern && !rule.pattern.test(val))         msg = rule.pattern_msg   || 'Invalid format';
    else if (rule.match) {
      const other = form.querySelector(`[name="${rule.match}"]`);
      if (other && val !== other.value.trim())                msg = rule.match_msg || 'Values do not match';
    }
    if (errEl) errEl.textContent = msg;
    if (msg) { field.style.borderColor = 'var(--danger)'; valid = false; }
    else       field.style.borderColor = '';
  }
  return valid;
}

/* ── HTML escape ─────────────────────────────────────────── */
function escapeHtml(str) {
  const d = document.createElement('div');
  d.appendChild(document.createTextNode(str));
  return d.innerHTML;
}

/* ── Auto-dismiss Alerts ─────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.alert-auto-dismiss').forEach(a => {
    setTimeout(() => {
      a.style.transition = 'opacity .5s ease, transform .5s ease';
      a.style.opacity = '0';
      a.style.transform = 'translateY(-8px)';
      setTimeout(() => a.remove(), 500);
    }, 4000);
  });

  initSidebar();

  document.querySelectorAll('[data-modal-close]').forEach(btn => {
    btn.addEventListener('click', () => closeModal(btn.dataset.modalClose));
  });
  document.querySelectorAll('[data-modal-open]').forEach(btn => {
    btn.addEventListener('click', () => openModal(btn.dataset.modalOpen));
  });

  // Stagger animate stat cards
  document.querySelectorAll('.stat-card').forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(14px)';
    setTimeout(() => {
      card.style.transition = 'opacity .4s cubic-bezier(.4,0,.2,1), transform .4s cubic-bezier(.4,0,.2,1)';
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, 60 + i * 55);
  });

  // Stagger animate table rows
  document.querySelectorAll('tbody tr').forEach((row, i) => {
    row.style.opacity = '0';
    setTimeout(() => {
      row.style.transition = `opacity .3s ease ${i * 25}ms`;
      row.style.opacity = '1';
    }, 120 + i * 25);
  });

  // Stagger animate event cards
  document.querySelectorAll('.event-card').forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    setTimeout(() => {
      card.style.transition = `opacity .5s ease ${i*80}ms, transform .5s cubic-bezier(.4,0,.2,1) ${i*80}ms`;
      card.style.opacity = '1';
      card.style.transform = 'translateY(0)';
    }, 100 + i * 80);
  });
});

/* ── AJAX helper ─────────────────────────────────────────── */
async function apiPost(url, data) {
  const formData = new FormData();
  for (const [k, v] of Object.entries(data)) formData.append(k, v);
  const res = await fetch(url, { method: 'POST', body: formData });
  return res.json();
}

/* ── Event search filter ─────────────────────────────────── */
function initEventSearch() {
  const searchInput = document.getElementById('event-search');
  const filterCat   = document.getElementById('filter-category');
  const cards       = document.querySelectorAll('.event-card-wrap');

  function filter() {
    const q   = searchInput ? searchInput.value.toLowerCase() : '';
    const cat = filterCat   ? filterCat.value.toLowerCase()   : '';
    let visible = 0;
    cards.forEach(card => {
      const title   = (card.dataset.title    || '').toLowerCase();
      const cardCat = (card.dataset.category || '').toLowerCase();
      const show    = (!q || title.includes(q)) && (!cat || cardCat === cat);
      card.style.display = show ? '' : 'none';
      if (show) visible++;
    });
    const empty = document.getElementById('events-empty');
    if (empty) empty.style.display = visible === 0 ? '' : 'none';
  }
  if (searchInput) searchInput.addEventListener('input', filter);
  if (filterCat)   filterCat.addEventListener('change', filter);
}

/* ── Role Selector ───────────────────────────────────────── */
function initRoleSelector() {
  const btns = document.querySelectorAll('.role-btn');
  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      btns.forEach(b => b.classList.remove('selected'));
      btn.classList.add('selected');
      const input = btn.querySelector('input');
      if (input) input.checked = true;
    });
  });
}

/* ── Counter Animation ───────────────────────────────────── */
function animateCounter(el, target, duration = 1200) {
  const start = performance.now();
  const update = (time) => {
    const elapsed  = time - start;
    const progress = Math.min(elapsed / duration, 1);
    const eased    = 1 - Math.pow(1 - progress, 3);
    el.textContent = Math.round(eased * target) + (el.dataset.suffix || '');
    if (progress < 1) requestAnimationFrame(update);
  };
  requestAnimationFrame(update);
}
