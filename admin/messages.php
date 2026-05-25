<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/messages.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id     = (int) ($_POST['id'] ?? 0);

    if ($action === 'read' && $id) {
        mark_message_read($id, true);
    } elseif ($action === 'unread' && $id) {
        mark_message_read($id, false);
    } elseif ($action === 'delete' && $id) {
        delete_message($id);
    } elseif ($action === 'read_all') {
        mark_all_messages_read();
    }
    redirect('messages.php');
}

$messages = get_messages();
$total    = count($messages);
$unread   = 0;
foreach ($messages as $m) {
    if ((int) $m['is_read'] === 0) $unread++;
}

$PAGE_TITLE = 'الرسائل';
$ACTIVE     = 'messages';
require __DIR__ . '/_header.php';
?>

<div class="intro">
  <p>الرسائل الواردة من نموذج التواصل في موقعك. تظهر الجديدة في الأعلى.</p>
</div>

<?php if ($total === 0): ?>
  <div class="empty">
    <div class="empty__icon"><?= ui_icon('mail', 30) ?></div>
    <h2>لا توجد رسائل بعد</h2>
    <p>عندما يرسل أحد العملاء رسالة عبر نموذج التواصل في موقعك، ستظهر هنا فورًا — ولن تضيع أبدًا.</p>
  </div>
<?php else: ?>
  <div class="msg-toolbar">
    <span class="msg-toolbar__count"><b><?= (int) $total ?></b> رسالة · <b><?= (int) $unread ?></b> غير مقروءة</span>
    <?php if ($unread): ?>
      <form method="post" class="inline">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="read_all">
        <button type="submit" class="btn btn--ghost btn--sm"><?= ui_icon('check', 15) ?> تحديد الكل كمقروء</button>
      </form>
    <?php endif; ?>
  </div>

  <div class="msg-list">
    <?php foreach ($messages as $m):
      $read    = (int) $m['is_read'] === 1;
      $initial = mb_strtoupper(mb_substr(trim($m['name']) !== '' ? $m['name'] : '؟', 0, 1, 'UTF-8'), 'UTF-8');
      $tel     = preg_replace('/[^\d+]/', '', (string) $m['phone']);
      $when    = date('Y/m/d — H:i', strtotime($m['created_at']));
    ?>
      <article class="msg-card <?= $read ? '' : 'is-unread' ?>">
        <div class="msg-card__top">
          <span class="msg-card__avatar"><?= esc($initial) ?></span>
          <div class="msg-card__id">
            <div class="msg-card__name">
              <?= esc($m['name']) ?>
              <span class="pill <?= $read ? 'pill--off' : 'pill--on' ?>"><span class="pill__dot"></span><?= $read ? 'مقروءة' : 'جديدة' ?></span>
            </div>
            <div class="msg-card__contact">
              <span><?= ui_icon('mail', 14) ?><a href="mailto:<?= esc($m['email']) ?>" dir="ltr"><?= esc($m['email']) ?></a></span>
              <?php if (trim((string) $m['phone']) !== ''): ?>
                <span><?= ui_icon('phone', 14) ?><a href="tel:<?= esc($tel) ?>" dir="ltr"><?= esc($m['phone']) ?></a></span>
              <?php endif; ?>
              <span><?= ui_icon('clock', 14) ?><span dir="ltr"><?= esc($when) ?></span></span>
            </div>
          </div>
          <div class="msg-card__actions">
            <a class="btn btn--ghost btn--sm" href="mailto:<?= esc($m['email']) ?>?subject=<?= rawurlencode('رد من emdatra') ?>"><?= ui_icon('mail-open', 15) ?> رد</a>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="<?= $read ? 'unread' : 'read' ?>">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button type="submit" class="iconbtn" title="<?= $read ? 'تحديد كغير مقروءة' : 'تحديد كمقروءة' ?>"><?= ui_icon($read ? 'mail' : 'check', 17) ?></button>
            </form>
            <form method="post" class="inline" onsubmit="return confirm('حذف هذه الرسالة نهائيًا؟');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
              <button type="submit" class="iconbtn iconbtn--danger" title="حذف"><?= ui_icon('trash', 17) ?></button>
            </form>
          </div>
        </div>
        <div class="msg-card__text"><?= esc($m['message']) ?></div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
