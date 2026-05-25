<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/chat.php';
require_login();

$id   = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$conv = chat_by_id($id);

if (!$conv) {
    if (isset($_GET['ajax']) || isset($_POST['ajax'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'notfound']);
        exit;
    }
    redirect('messages.php');
}

/* ---------- AJAX: poll for new messages ---------- */
if (($_GET['ajax'] ?? '') === 'poll') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    $after = (int) ($_GET['after'] ?? 0);
    $rows  = chat_messages($id, $after);
    chat_seen_by_admin($id);
    echo json_encode(['ok' => true, 'messages' => array_map('chat_msg_json', $rows)], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- reply (AJAX or normal form) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'reply') {
    verify_csrf();
    $body = trim($_POST['body'] ?? '');
    $ajax = !empty($_POST['ajax']);
    $mid  = 0;
    if ($body !== '') {
        $body = mb_substr($body, 0, 4000);
        $mid  = chat_add_message($id, 'admin', $body);
    }
    if ($ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            $mid ? ['ok' => true, 'message' => chat_msg_json(['id' => $mid, 'sender' => 'admin', 'body' => $body, 'created_at' => date('Y-m-d H:i:s')])]
                 : ['ok' => false],
            JSON_UNESCAPED_UNICODE
        );
        exit;
    }
    redirect('conversation.php?id=' . $id);
}

/* ---------- normal page ---------- */
chat_seen_by_admin($id);
$messages = chat_messages($id, 0);
$initial  = mb_strtoupper(mb_substr(trim($conv['name']) !== '' ? $conv['name'] : '؟', 0, 1, 'UTF-8'), 'UTF-8');

$PAGE_TITLE = 'محادثة مع ' . $conv['name'];
$ACTIVE     = 'messages';
require __DIR__ . '/_header.php';
?>

<a href="messages.php" class="conv-back"><?= ui_icon('back', 16) ?> كل المحادثات</a>

<div class="conv-head-card">
  <span class="conv-who__av"><?= esc($initial) ?></span>
  <div class="conv-who">
    <div class="conv-who__name"><?= esc($conv['name']) ?><?php if (($conv['source'] ?? '') === 'form'): ?> <span class="conv-tag">نموذج</span><?php endif; ?></div>
    <div class="conv-who__contact">
      <?php if (!empty($conv['email'])): ?><a href="mailto:<?= esc($conv['email']) ?>" dir="ltr"><?= ui_icon('mail', 13) ?> <?= esc($conv['email']) ?></a><?php endif; ?>
      <?php if (!empty($conv['phone'])): ?><a href="tel:<?= esc(preg_replace('/[^\d+]/', '', $conv['phone'])) ?>" dir="ltr"><?= ui_icon('phone', 13) ?> <?= esc($conv['phone']) ?></a><?php endif; ?>
    </div>
  </div>
</div>

<div class="conv-thread" id="convThread">
  <?php foreach ($messages as $m): ?>
    <div class="conv-msg conv-msg--<?= $m['sender'] === 'admin' ? 'admin' : 'user' ?>" data-id="<?= (int) $m['id'] ?>">
      <div class="conv-msg__body"><?= esc($m['body']) ?></div>
      <span class="conv-msg__time"><?= esc(date('H:i', strtotime($m['created_at']))) ?></span>
    </div>
  <?php endforeach; ?>
</div>

<form class="conv-reply" id="replyForm" method="post" action="conversation.php?id=<?= (int) $id ?>">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="reply">
  <textarea name="body" id="replyBox" rows="1" placeholder="اكتب ردك للزبون..."></textarea>
  <button type="submit" class="conv-reply__send" title="إرسال"><?= ui_icon('send', 19) ?></button>
</form>

<script>
(function () {
  var id = <?= (int) $id ?>, csrf = <?= json_encode(csrf_token()) ?>;
  var thread = document.getElementById('convThread');
  var seen = {}, lastId = 0;
  Array.prototype.forEach.call(thread.querySelectorAll('[data-id]'), function (e) {
    var i = +e.getAttribute('data-id'); seen[i] = 1; if (i > lastId) lastId = i;
  });
  function add(m) {
    if (seen[m.id]) return; seen[m.id] = 1; if (m.id > lastId) lastId = m.id;
    var d = document.createElement('div');
    d.className = 'conv-msg conv-msg--' + (m.sender === 'admin' ? 'admin' : 'user');
    d.setAttribute('data-id', m.id);
    var b = document.createElement('div'); b.className = 'conv-msg__body'; b.textContent = m.body; d.appendChild(b);
    var t = document.createElement('span'); t.className = 'conv-msg__time'; t.textContent = m.time; d.appendChild(t);
    thread.appendChild(d); thread.scrollTop = thread.scrollHeight;
  }
  function poll() {
    if (document.hidden) return;
    fetch('conversation.php?id=' + id + '&ajax=poll&after=' + lastId)
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d && d.ok) (d.messages || []).forEach(add); })
      .catch(function () {});
  }
  setInterval(poll, 5000);
  document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });

  var form = document.getElementById('replyForm'), box = document.getElementById('replyBox'), btn = form.querySelector('button');
  function submit() {
    var v = box.value.trim(); if (!v) return; btn.disabled = true;
    var body = 'action=reply&ajax=1&id=' + id + '&csrf=' + encodeURIComponent(csrf) + '&body=' + encodeURIComponent(v);
    fetch('conversation.php?id=' + id, { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body })
      .then(function (r) { return r.json(); })
      .then(function (d) { btn.disabled = false; if (d && d.ok && d.message) { add(d.message); box.value = ''; box.style.height = 'auto'; box.focus(); } })
      .catch(function () { btn.disabled = false; });
  }
  form.addEventListener('submit', function (e) { e.preventDefault(); submit(); });
  box.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); submit(); } });
  box.addEventListener('input', function () { box.style.height = 'auto'; box.style.height = Math.min(box.scrollHeight, 140) + 'px'; });
  thread.scrollTop = thread.scrollHeight;
})();
</script>

<?php require __DIR__ . '/_footer.php'; ?>
