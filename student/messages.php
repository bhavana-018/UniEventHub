<?php
require_once '../includes/auth.php';
require_once '../includes/db.php';
requireRole('student', '../login.php');

$uid = $_SESSION['user_id'];

/* ── Counts for sidebar badges ── */
$unreadNotif = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0");
$unreadNotif->execute([$uid]); $unreadNotif = $unreadNotif->fetchColumn();

$unreadMsg = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
$unreadMsg->execute([$uid]); $unreadMsg = $unreadMsg->fetchColumn();

/* ── Current user ── */
$user = $pdo->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$uid]); $user = $user->fetch();

/* ── All conversations (latest message per person) ── */
$conversations = $pdo->prepare("
    SELECT
        u.id, u.full_name, u.role, u.department,
        m.message   AS last_message,
        m.sent_at   AS last_time,
        m.sender_id,
        SUM(CASE WHEN m2.is_read=0 AND m2.receiver_id=? THEN 1 ELSE 0 END) AS unread_count
    FROM users u
    JOIN messages m ON (
        (m.sender_id=u.id AND m.receiver_id=?)
        OR (m.sender_id=? AND m.receiver_id=u.id)
    )
    JOIN messages m2 ON (
        (m2.sender_id=u.id AND m2.receiver_id=?)
        OR (m2.sender_id=? AND m2.receiver_id=u.id)
    )
    WHERE u.id != ?
    AND m.sent_at = (
        SELECT MAX(mx.sent_at) FROM messages mx
        WHERE (mx.sender_id=u.id AND mx.receiver_id=?)
           OR (mx.sender_id=? AND mx.receiver_id=u.id)
    )
    GROUP BY u.id, u.full_name, u.role, u.department, m.message, m.sent_at, m.sender_id
    ORDER BY m.sent_at DESC
");
$conversations->execute([$uid,$uid,$uid,$uid,$uid,$uid,$uid,$uid]);
$conversations = $conversations->fetchAll();

/* ── Contacts student can message (admins + organizers) ── */
$contacts = $pdo->query("SELECT id, full_name, role, department FROM users WHERE role IN ('admin','organizer') AND is_active=1 ORDER BY role, full_name")->fetchAll();

/* ── Active conversation ── */
$activeId     = (int)($_GET['with'] ?? 0);
$activeUser   = null;
$chatMessages = [];
$lastMsgId    = 0;

if ($activeId) {
    $activeUser = $pdo->prepare("SELECT * FROM users WHERE id=? AND is_active=1");
    $activeUser->execute([$activeId]); $activeUser = $activeUser->fetch();

    if ($activeUser) {
        $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=?")->execute([$activeId, $uid]);
        $chatMessages = $pdo->prepare("
            SELECT m.*, u.full_name AS sender_name, u.role AS sender_role
            FROM messages m
            JOIN users u ON m.sender_id=u.id
            WHERE (m.sender_id=? AND m.receiver_id=?)
               OR (m.sender_id=? AND m.receiver_id=?)
            ORDER BY m.sent_at ASC
        ");
        $chatMessages->execute([$uid,$activeId,$activeId,$uid]);
        $chatMessages = $chatMessages->fetchAll();
        if (!empty($chatMessages)) $lastMsgId = end($chatMessages)['id'];
    }
}

$roleColors = ['student'=>'student','organizer'=>'organizer','admin'=>'admin'];
$activePage  = 'messages';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Messages — UniEventHub</title>
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/chat.css">
</head>
<body>
<div class="dashboard-layout">

  <?php include 'includes/sidebar.php'; ?>

  <main class="main-content" style="display:flex;flex-direction:column;overflow:hidden">

    <!-- Topbar -->
    <div class="topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button id="sidebar-toggle" style="background:none;border:none;cursor:pointer;padding:6px;color:var(--text-3);display:flex;border-radius:var(--r-sm);transition:var(--t)" onmouseenter="this.style.background='rgba(255,255,255,.06)'" onmouseleave="this.style.background='none'">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        </button>
        <div class="topbar-title">Messages</div>
      </div>
      <button class="new-msg-btn" onclick="openModal('new-msg-modal')">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Message
      </button>
    </div>

    <!-- Inbox Layout -->
    <div class="inbox-layout">

      <!-- Left: Conversations -->
      <div class="conversations-panel">
        <div class="conversations-header">
          <h2>Conversations</h2>
          <?php if ($unreadMsg > 0): ?>
          <span class="badge badge-primary badge-nodot" style="font-size:.66rem"><?= $unreadMsg ?> unread</span>
          <?php endif; ?>
        </div>
        <div class="conversations-search">
          <div class="conversations-search-wrap">
            <svg class="conversations-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="conv-search" placeholder="Search conversations...">
          </div>
        </div>
        <div class="conversations-list" id="conv-list">
          <?php if (empty($conversations)): ?>
          <div style="padding:40px 16px;text-align:center;color:var(--text-5)">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="margin:0 auto 12px;display:block;opacity:.3"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <div style="font-size:.84rem;color:var(--text-4);font-weight:600">No conversations yet</div>
            <div style="font-size:.76rem;color:var(--text-5);margin-top:4px">Start a new message above</div>
          </div>
          <?php else: ?>
          <?php foreach ($conversations as $c): ?>
          <a href="messages.php?with=<?= $c['id'] ?>"
             class="conversation-item <?= $activeId===$c['id']?'active':'' ?> <?= $c['unread_count']>0?'unread':'' ?>"
             data-name="<?= htmlspecialchars(strtolower($c['full_name'])) ?>">
            <div class="conv-avatar <?= $roleColors[$c['role']] ?? 'student' ?>">
              <?= strtoupper(substr($c['full_name'],0,1)) ?>
            </div>
            <div class="conv-info">
              <div class="conv-name"><?= htmlspecialchars($c['full_name']) ?></div>
              <div class="conv-preview">
                <?= $c['sender_id']==$uid ? '<span style="color:var(--text-5)">You: </span>' : '' ?><?= htmlspecialchars(mb_substr($c['last_message'],0,38)).(mb_strlen($c['last_message'])>38?'…':'') ?>
              </div>
            </div>
            <div class="conv-meta">
              <div class="conv-time"><?= date('g:i A', strtotime($c['last_time'])) ?></div>
              <?php if ($c['unread_count'] > 0): ?>
              <div class="conv-badge"><?= $c['unread_count'] ?></div>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>

      <!-- Right: Chat -->
      <div class="chat-panel">
        <?php if ($activeUser): ?>

        <!-- Chat Header -->
        <div class="chat-header">
          <div class="conv-avatar <?= $roleColors[$activeUser['role']] ?? 'student' ?>" style="width:42px;height:42px">
            <?= strtoupper(substr($activeUser['full_name'],0,1)) ?>
          </div>
          <div class="chat-header-info">
            <div class="chat-header-name"><?= htmlspecialchars($activeUser['full_name']) ?></div>
            <div class="chat-header-role"><?= ucfirst($activeUser['role']) ?><?= $activeUser['department'] ? ' · '.$activeUser['department'] : '' ?></div>
          </div>
        </div>

        <!-- Messages -->
        <div class="chat-messages" id="chat-messages">
          <?php
          $prevDate = '';
          foreach ($chatMessages as $msg):
            $msgDate = date('Y-m-d', strtotime($msg['sent_at']));
            $isSent  = $msg['sender_id'] == $uid;
            if ($msgDate !== $prevDate):
              $prevDate = $msgDate;
              $label = $msgDate === date('Y-m-d') ? 'Today'
                     : ($msgDate === date('Y-m-d', strtotime('-1 day')) ? 'Yesterday'
                     : date('M j, Y', strtotime($msg['sent_at'])));
          ?>
          <div class="msg-date-divider"><?= $label ?></div>
          <?php endif; ?>
          <div class="msg-wrap <?= $isSent ? 'sent' : 'received' ?>">
            <?php if (!$isSent): ?>
            <div class="msg-avatar-sm <?= $roleColors[$msg['sender_role']] ?? 'student' ?>">
              <?= strtoupper(substr($msg['sender_name'],0,1)) ?>
            </div>
            <?php endif; ?>
            <div class="msg-body">
              <div class="msg-bubble"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
              <div class="msg-time">
                <?= date('g:i A', strtotime($msg['sent_at'])) ?>
                <?php if ($isSent): ?>
                <span class="msg-read-tick"><?= $msg['is_read'] ? ' ✓✓' : ' ✓' ?></span>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (empty($chatMessages)): ?>
          <div style="text-align:center;padding:48px 24px;color:var(--text-5)">
            <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin:0 auto 16px;display:block;opacity:.2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <div style="font-size:.84rem;color:var(--text-4);font-weight:600">Start the conversation</div>
            <div style="font-size:.76rem;color:var(--text-5);margin-top:4px">Send a message to <?= htmlspecialchars($activeUser['full_name']) ?></div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Input Area -->
        <div class="chat-input-area">
          <div class="chat-input-wrap">
            <textarea
              class="chat-input"
              id="msg-input"
              rows="1"
              placeholder="Type a message… (Enter to send, Shift+Enter for new line)"></textarea>
          </div>
          <button class="chat-send-btn" id="send-btn" onclick="sendMessage()" title="Send">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
          </button>
        </div>

        <?php else: ?>
        <!-- No chat selected -->
        <div class="chat-empty">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <h3>Select a conversation</h3>
          <p>Choose from your existing conversations on the left, or start a new message with an organizer or admin.</p>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<!-- New Message Modal -->
<div class="modal-overlay" id="new-msg-modal">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">New Message</div>
      <button class="modal-close" data-modal-close="new-msg-modal">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Search Recipient</label>
        <input type="text" id="contact-search" class="form-control" placeholder="Search by name or role…">
      </div>
      <div class="user-picker-list" id="contact-list">
        <?php foreach ($contacts as $c): ?>
        <div class="user-picker-item"
             data-id="<?= $c['id'] ?>"
             data-name="<?= htmlspecialchars(strtolower($c['full_name'])) ?>"
             onclick="selectContact(<?= $c['id'] ?>, '<?= htmlspecialchars($c['full_name'], ENT_QUOTES) ?>')">
          <div class="conv-avatar <?= $roleColors[$c['role']] ?? 'admin' ?>" style="width:36px;height:36px;font-size:.8rem">
            <?= strtoupper(substr($c['full_name'],0,1)) ?>
          </div>
          <div>
            <div style="font-weight:600;font-size:.84rem;color:var(--text-1)"><?= htmlspecialchars($c['full_name']) ?></div>
            <div style="font-size:.74rem;color:var(--text-4)"><?= ucfirst($c['role']) ?><?= $c['department'] ? ' · '.$c['department'] : '' ?></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($contacts)): ?>
        <div style="text-align:center;padding:24px;color:var(--text-5)">No contacts available</div>
        <?php endif; ?>
      </div>
      <div id="selected-contact" style="display:none;margin-top:14px;padding:10px 14px;background:var(--indigo-soft);border:1px solid var(--indigo);border-radius:var(--r);font-size:.84rem;font-weight:600;color:var(--indigo-light)"></div>
      <div class="form-group" style="margin-top:14px">
        <label class="form-label">Message</label>
        <textarea id="new-msg-text" class="form-control" rows="3" placeholder="Type your message…"></textarea>
      </div>
      <div id="new-msg-alert" style="display:none;margin-bottom:10px"></div>
      <div style="display:flex;gap:10px">
        <button onclick="sendNewMessage()" class="btn btn-primary" style="flex:1" id="new-msg-btn">Send Message</button>
        <button class="btn btn-ghost" data-modal-close="new-msg-modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script src="../js/main.js"></script>
<script>
const MY_ID     = <?= $uid ?>;
const ACTIVE_ID = <?= $activeId ?: 'null' ?>;
let lastId      = <?= $lastMsgId ?>;
let selectedContactId = null;

/* ── Auto-scroll ── */
function scrollToBottom(smooth = false) {
  const el = document.getElementById('chat-messages');
  if (!el) return;
  el.scrollTo({ top: el.scrollHeight, behavior: smooth ? 'smooth' : 'auto' });
}
scrollToBottom();

/* ── Auto-resize textarea ── */
const msgInput = document.getElementById('msg-input');
if (msgInput) {
  msgInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 140) + 'px';
  });
  msgInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      sendMessage();
    }
  });
  // Focus the input automatically when chat is open
  <?php if ($activeId): ?>
  setTimeout(() => msgInput.focus(), 80);
  <?php endif; ?>
}

/* ── Send message ── */
async function sendMessage() {
  if (!ACTIVE_ID) return;
  const input = document.getElementById('msg-input');
  const text  = input.value.trim();
  if (!text) return;

  const btn  = document.getElementById('send-btn');
  const sent = text;
  btn.disabled = true;
  input.value  = '';
  input.style.height = 'auto';

  appendBubble('sent', sent, 'Just now', '✓');

  try {
    const fd = new FormData();
    fd.append('receiver_id', ACTIVE_ID);
    fd.append('message', sent);
    const res  = await fetch('../php/send_message.php', { method:'POST', body:fd });
    const data = await res.json();
    if (!data.success) showToast(data.message || 'Failed to send.', 'error');
    else if (data.id) lastId = Math.max(lastId, data.id);
  } catch { showToast('Failed to send message.', 'error'); }
  finally  { btn.disabled = false; }
}

/* ── Append bubble to chat ── */
function appendBubble(side, text, time, tick) {
  const chatEl = document.getElementById('chat-messages');
  if (!chatEl) return;

  // Remove "start conversation" placeholder if present
  const placeholder = chatEl.querySelector('[data-placeholder]');
  if (placeholder) placeholder.remove();

  const wrap = document.createElement('div');
  wrap.className = 'msg-wrap ' + side;

  const safeText = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/\n/g,'<br>');
  const tickHtml = tick ? `<span class="msg-read-tick"> ${tick}</span>` : '';

  if (side === 'received') {
    const role = '<?= $activeUser ? ($roleColors[$activeUser['role']] ?? 'student') : 'student' ?>';
    const init = '<?= $activeUser ? strtoupper(substr($activeUser['full_name'],0,1)) : '?' ?>';
    wrap.innerHTML = `
      <div class="msg-avatar-sm ${role}">${init}</div>
      <div class="msg-body">
        <div class="msg-bubble">${safeText}</div>
        <div class="msg-time">${time}</div>
      </div>`;
  } else {
    wrap.innerHTML = `
      <div class="msg-body">
        <div class="msg-bubble">${safeText}</div>
        <div class="msg-time">${time}${tickHtml}</div>
      </div>`;
  }

  chatEl.appendChild(wrap);
  scrollToBottom(true);
}

/* ── Poll for new incoming messages ── */
<?php if ($activeId): ?>
setInterval(async () => {
  try {
    const res  = await fetch(`../php/poll_messages.php?with=<?= $activeId ?>&last=${lastId}`);
    const data = await res.json();
    if (data.messages && data.messages.length > 0) {
      data.messages.forEach(m => {
        appendBubble('received', m.raw || m.message, m.time, '');
        lastId = Math.max(lastId, m.id || 0);
      });
    }
  } catch {}
}, 4000);
<?php endif; ?>

/* ── Conversation search ── */
document.getElementById('conv-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.conversation-item').forEach(item => {
    item.style.display = item.dataset.name.includes(q) ? '' : 'none';
  });
});

/* ── Contact search in modal ── */
document.getElementById('contact-search').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.user-picker-item').forEach(item => {
    item.style.display = item.dataset.name.includes(q) ? '' : 'none';
  });
});

/* ── Select contact ── */
function selectContact(id, name) {
  selectedContactId = id;
  document.querySelectorAll('.user-picker-item').forEach(i => i.classList.remove('selected'));
  const item = document.querySelector(`.user-picker-item[data-id="${id}"]`);
  if (item) item.classList.add('selected');
  const sel = document.getElementById('selected-contact');
  sel.style.display = 'block';
  sel.textContent   = '✓ Selected: ' + name;
}

/* ── Send new message from modal ── */
async function sendNewMessage() {
  if (!selectedContactId) { showToast('Please select a recipient first.', 'error'); return; }
  const text  = document.getElementById('new-msg-text').value.trim();
  const alertEl = document.getElementById('new-msg-alert');
  const btn     = document.getElementById('new-msg-btn');
  if (!text) {
    alertEl.className = 'alert alert-danger';
    alertEl.innerHTML = '✕ Please enter a message.';
    alertEl.style.display = 'flex'; return;
  }
  btn.disabled = true; btn.textContent = 'Sending…';
  try {
    const fd = new FormData();
    fd.append('receiver_id', selectedContactId);
    fd.append('message', text);
    const res  = await fetch('../php/send_message.php', { method:'POST', body:fd });
    const data = await res.json();
    if (data.success) {
      window.location.href = 'messages.php?with=' + selectedContactId;
    } else {
      alertEl.className = 'alert alert-danger';
      alertEl.innerHTML = '✕ ' + (data.message || 'Failed to send.');
      alertEl.style.display = 'flex';
      btn.disabled = false; btn.textContent = 'Send Message';
    }
  } catch {
    showToast('Network error. Please try again.', 'error');
    btn.disabled = false; btn.textContent = 'Send Message';
  }
}

document.addEventListener('DOMContentLoaded', initSidebar);
</script>
</body>
</html>
