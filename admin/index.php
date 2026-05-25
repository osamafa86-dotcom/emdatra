<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/seed.php';
require_login();

$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'seed') {
        $flash = seed_default_content() ? 'تم استيراد محتوى موقعك الحالي بنجاح.' : 'المحتوى موجود بالفعل.';
    } elseif ($action === 'toggle' && $id) {
        db()->prepare('UPDATE emd_blocks SET is_visible = 1 - is_visible WHERE id = ?')->execute([$id]);
    } elseif ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM emd_blocks WHERE id = ?')->execute([$id]);
    } elseif ($action === 'move' && $id) {
        $dir = ($_POST['dir'] ?? '') === 'up' ? 'up' : 'down';
        $cur = db()->prepare('SELECT id, page, sort_order FROM emd_blocks WHERE id = ?');
        $cur->execute([$id]);
        if ($row = $cur->fetch()) {
            $sql = $dir === 'up'
                ? 'SELECT id, sort_order FROM emd_blocks WHERE page = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1'
                : 'SELECT id, sort_order FROM emd_blocks WHERE page = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1';
            $q = db()->prepare($sql);
            $q->execute([$row['page'], $row['sort_order']]);
            if ($neighbor = $q->fetch()) {
                $upd = db()->prepare('UPDATE emd_blocks SET sort_order = ? WHERE id = ?');
                $upd->execute([$neighbor['sort_order'], $row['id']]);
                $upd->execute([$row['sort_order'], $neighbor['id']]);
            }
        }
    }
    if (!$flash) {
        redirect('index.php');
    }
}

$blocks = get_blocks('home');
$count  = count($blocks);

$PAGE_TITLE = 'أقسام الصفحة الرئيسية';
$ACTIVE     = 'dashboard';
require __DIR__ . '/_header.php';
?>

<?php if ($flash): ?>
  <div class="msg msg--ok"><?= ui_icon('save', 18) ?> <?= esc($flash) ?></div>
<?php endif; ?>

<div class="intro">
  <p>تحكّم بترتيب الأقسام وإظهارها، واضغط «تعديل» لتغيير النصوص بالعربية والإنجليزية.</p>
</div>

<?php if ($count === 0): ?>
  <div class="empty">
    <div class="empty__icon"><?= ui_icon('layers', 30) ?></div>
    <h2>لنبدأ باستيراد محتوى موقعك الحالي</h2>
    <p>سننقل كل أقسام موقعك (البانر، الأرقام، من نحن، الخدمات، لماذا نحن، التواصل، الدعوة) إلى لوحة التحكم لتعديلها بسهولة.</p>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="action" value="seed">
      <button type="submit" class="btn"><?= ui_icon('plus', 18) ?> استيراد المحتوى الحالي</button>
    </form>
  </div>
<?php else: ?>
  <div class="sections">
    <?php foreach ($blocks as $i => $b):
      $on = (int)$b['is_visible'] === 1;
    ?>
      <div class="section-card <?= $on ? '' : 'is-hidden' ?>">
        <div class="section-card__handle">
          <form method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="move">
            <input type="hidden" name="dir" value="up">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button type="submit" title="تحريك لأعلى" <?= $i === 0 ? 'disabled' : '' ?>><?= ui_icon('up', 15) ?></button>
          </form>
          <form method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="move">
            <input type="hidden" name="dir" value="down">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button type="submit" title="تحريك لأسفل" <?= $i === $count - 1 ? 'disabled' : '' ?>><?= ui_icon('down', 15) ?></button>
          </form>
        </div>

        <div class="section-card__icon"><?= block_type_icon($b['type'], 24) ?></div>

        <div class="section-card__body">
          <div class="section-card__title"><?= esc(block_label($b['type'])) ?></div>
          <div class="section-card__meta"><?= esc(block_label($b['type'], 'en')) ?></div>
        </div>

        <div class="section-card__actions">
          <span class="pill <?= $on ? 'pill--on' : 'pill--off' ?>"><span class="pill__dot"></span><?= $on ? 'ظاهر' : 'مخفي' ?></span>
          <a class="btn btn--ghost btn--sm" href="block-edit.php?id=<?= (int)$b['id'] ?>"><?= ui_icon('edit', 15) ?> تعديل</a>
          <form method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button type="submit" class="iconbtn" title="<?= $on ? 'إخفاء' : 'إظهار' ?>"><?= ui_icon($on ? 'eye-off' : 'eye', 17) ?></button>
          </form>
          <form method="post" class="inline" onsubmit="return confirm('حذف هذا القسم نهائيًا؟');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
            <button type="submit" class="iconbtn iconbtn--danger" title="حذف"><?= ui_icon('trash', 17) ?></button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="hint" style="margin-top:22px">
    الخطوة التالية: تشغيل الموقع الجديد المرتبط بلوحة التحكم لتظهر تعديلاتك مباشرةً، ثم الإعدادات العامة وإضافة أقسام جديدة.
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
