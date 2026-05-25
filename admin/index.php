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
        if (seed_default_content()) {
            $flash = 'تم استيراد محتوى موقعك الحالي بنجاح.';
        } else {
            $flash = 'المحتوى موجود بالفعل.';
        }
    } elseif ($action === 'toggle' && $id) {
        db()->prepare('UPDATE emd_blocks SET is_visible = 1 - is_visible WHERE id = ?')->execute([$id]);
    } elseif ($action === 'delete' && $id) {
        db()->prepare('DELETE FROM emd_blocks WHERE id = ?')->execute([$id]);
    } elseif ($action === 'move' && $id) {
        $dir = $_POST['dir'] === 'up' ? 'up' : 'down';
        $cur = db()->prepare('SELECT id, page, sort_order FROM emd_blocks WHERE id = ?');
        $cur->execute([$id]);
        if ($row = $cur->fetch()) {
            if ($dir === 'up') {
                $q = db()->prepare('SELECT id, sort_order FROM emd_blocks WHERE page = ? AND sort_order < ? ORDER BY sort_order DESC LIMIT 1');
            } else {
                $q = db()->prepare('SELECT id, sort_order FROM emd_blocks WHERE page = ? AND sort_order > ? ORDER BY sort_order ASC LIMIT 1');
            }
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
$brand  = setting('brand', ['logo' => 'assets/logo.png']);
$logo   = '../' . ($brand['logo'] ?? 'assets/logo.png');
$count  = count($blocks);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>لوحة التحكم — emdatra</title>
<link rel="icon" type="image/png" href="<?= esc($logo) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="topbar">
  <div class="topbar__inner">
    <div class="topbar__brand">
      <img src="<?= esc($logo) ?>" alt="emdatra">
      <div><b>emdatra</b><span>لوحة التحكم</span></div>
    </div>
    <div class="topbar__actions">
      <span class="topbar__user"><?= esc($_SESSION['admin_user'] ?? '') ?></span>
      <a class="btn btn--ghost btn--sm" href="../" target="_blank" rel="noopener">عرض الموقع</a>
      <a class="btn btn--ghost btn--sm" href="logout.php">خروج</a>
    </div>
  </div>
</div>

<div class="wrap">

  <?php if ($flash): ?>
    <div class="msg msg--ok"><?= esc($flash) ?></div>
  <?php endif; ?>

  <div class="page-head">
    <div>
      <h1>أقسام الصفحة الرئيسية</h1>
      <p>تحكّم بترتيب الأقسام وإظهارها. التعديل على المحتوى والصور في الخطوة التالية.</p>
    </div>
  </div>

  <?php if ($count === 0): ?>
    <div class="empty">
      <h2>لنبدأ باستيراد محتوى موقعك الحالي</h2>
      <p>سننقل كل أقسام موقعك (البانر، الأرقام، من نحن، الخدمات، لماذا نحن، التواصل، الدعوة) إلى لوحة التحكم لتتمكن من تعديلها لاحقًا.</p>
      <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="seed">
        <button type="submit" class="btn">استيراد المحتوى الحالي</button>
      </form>
    </div>
  <?php else: ?>
    <div class="blocks">
      <?php foreach ($blocks as $i => $b):
        $data = block_data($b);
        $on   = (int)$b['is_visible'] === 1;
      ?>
        <div class="block-row <?= $on ? '' : 'is-hidden' ?>">
          <div class="block-row__order">
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="move">
              <input type="hidden" name="dir" value="up">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button type="submit" title="أعلى" <?= $i === 0 ? 'disabled' : '' ?>>&#9650;</button>
            </form>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="move">
              <input type="hidden" name="dir" value="down">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button type="submit" title="أسفل" <?= $i === $count - 1 ? 'disabled' : '' ?>>&#9660;</button>
            </form>
          </div>

          <div class="block-row__icon"><?= esc(mb_substr(block_label($b['type']), 0, 1)) ?></div>

          <div class="block-row__main">
            <div class="block-row__title"><?= esc(block_label($b['type'])) ?></div>
            <div class="block-row__type"><?= esc(block_label($b['type'], 'en')) ?> · <?= esc($b['type']) ?></div>
          </div>

          <div class="block-row__actions">
            <span class="tag <?= $on ? 'tag--on' : 'tag--off' ?>"><?= $on ? 'ظاهر' : 'مخفي' ?></span>
            <a class="btn btn--sm" href="block-edit.php?id=<?= (int)$b['id'] ?>">تعديل</a>
            <form method="post" class="inline">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="toggle">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button type="submit" class="btn btn--ghost btn--sm"><?= $on ? 'إخفاء' : 'إظهار' ?></button>
            </form>
            <form method="post" class="inline" onsubmit="return confirm('حذف هذا القسم نهائيًا؟');">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button type="submit" class="btn btn--danger btn--sm">حذف</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="hint">
      الخطوة التالية: تعديل نصوص وصور كل قسم، وإضافة أقسام جديدة، ثم تشغيل الموقع الجديد المرتبط بلوحة التحكم.
    </div>
  <?php endif; ?>

</div>
</body>
</html>
