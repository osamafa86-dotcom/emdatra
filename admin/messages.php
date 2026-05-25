<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/chat.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'delete' && (int) ($_POST['id'] ?? 0)) {
        chat_delete_conversation((int) $_POST['id']);
    }
    redirect('messages.php');
}

$conversations = get_conversations_safe();
$total  = count($conversations);
$unread = 0;
foreach ($conversations as $c) {
    if ((int) $c['unread'] > 0) $unread++;
}

function get_conversations_safe()
{
    try { return chat_conversations(); } catch (Throwable $e) { return []; }
}

function conv_when($ts)
{
    $t = strtotime($ts);
    return date('Y/m/d', $t) === date('Y/m/d') ? date('H:i', $t) : date('Y/m/d', $t);
}

$PAGE_TITLE = 'المحادثات';
$ACTIVE     = 'messages';
require __DIR__ . '/_header.php';
?>

<div class="intro">
  <p>المحادثات الواردة من موقعك. اضغط على أي محادثة للرد عليها — وسيظهر ردّك للزبون داخل الموقع مباشرةً.</p>
</div>

<?php if ($total === 0): ?>
  <div class="empty">
    <div class="empty__icon"><?= ui_icon('mail', 30) ?></div>
    <h2>لا توجد محادثات بعد</h2>
    <p>عندما يبدأ أحد الزوار محادثة عبر الموقع، ستظهر هنا — وتستطيع الرد عليه مباشرةً.</p>
  </div>
<?php else: ?>
  <div class="conv-list">
    <?php foreach ($conversations as $c):
      $unreadN = (int) $c['unread'];
      $initial = mb_strtoupper(mb_substr(trim($c['name']) !== '' ? $c['name'] : '؟', 0, 1, 'UTF-8'), 'UTF-8');
      $preview = trim(preg_replace('/\s+/', ' ', (string) $c['last_body']));
      $preview = mb_substr($preview, 0, 64, 'UTF-8');
      $mine    = ($c['last_sender'] ?? '') === 'admin';
    ?>
      <div class="conv-item <?= $unreadN ? 'is-unread' : '' ?>">
        <a class="conv-item__link" href="conversation.php?id=<?= (int) $c['id'] ?>">
          <span class="conv-item__av"><?= esc($initial) ?></span>
          <span class="conv-item__main">
            <span class="conv-item__row">
              <span class="conv-item__name">
                <?= esc($c['name']) ?>
                <?php if (($c['source'] ?? '') === 'form'): ?><span class="conv-tag">نموذج</span><?php endif; ?>
              </span>
              <span class="conv-item__time"><?= esc(conv_when($c['updated_at'])) ?></span>
            </span>
            <span class="conv-item__row">
              <span class="conv-item__preview"><?php if ($mine): ?><span class="conv-item__you">أنت: </span><?php endif; ?><?= esc($preview) ?></span>
              <?php if ($unreadN): ?><span class="conv-item__badge"><?= $unreadN ?></span><?php endif; ?>
            </span>
          </span>
        </a>
        <form method="post" class="inline conv-item__del" onsubmit="return confirm('حذف هذه المحادثة وكل رسائلها نهائيًا؟');">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int) $c['id'] ?>">
          <button type="submit" class="iconbtn iconbtn--danger" title="حذف"><?= ui_icon('trash', 16) ?></button>
        </form>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
