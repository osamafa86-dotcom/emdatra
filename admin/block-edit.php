<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/blocks.php';
require_login();

$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM emd_blocks WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$block = $stmt->fetch();
if (!$block) {
    redirect('index.php');
}

$type   = $block['type'];
$schema = block_schema($type);
$saved  = isset($_GET['saved']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $content = block_data($block);
    $icons   = icon_options();

    foreach ($schema['fields'] as $field) {
        if ($field['kind'] === 'bil' || $field['kind'] === 'bil_area') {
            $content[$field['ar']] = trim($_POST['content'][$field['ar']] ?? '');
            $content[$field['en']] = trim($_POST['content'][$field['en']] ?? '');
        } elseif ($field['kind'] === 'icon') {
            $v = $_POST['content'][$field['key']] ?? '';
            $content[$field['key']] = isset($icons[$v]) ? $v : 'globe';
        } else {
            $content[$field['key']] = trim($_POST['content'][$field['key']] ?? '');
        }
    }

    foreach ($schema['repeaters'] as $rep) {
        $rows  = $_POST['rep'][$rep['key']] ?? [];
        $clean = [];
        if (is_array($rows)) {
            foreach ($rows as $row) {
                if (!is_array($row)) continue;
                $obj   = [];
                $empty = true;
                foreach ($rep['subfields'] as $sf) {
                    if ($sf['kind'] === 'bil' || $sf['kind'] === 'bil_area') {
                        $a = trim($row[$sf['ar']] ?? '');
                        $e = trim($row[$sf['en']] ?? '');
                        $obj[$sf['ar']] = $a;
                        $obj[$sf['en']] = $e;
                        if ($a !== '' || $e !== '') $empty = false;
                    } elseif ($sf['kind'] === 'icon') {
                        $v = $row[$sf['key']] ?? '';
                        $obj[$sf['key']] = isset($icons[$v]) ? $v : 'globe';
                    } else {
                        $v = trim($row[$sf['key']] ?? '');
                        $obj[$sf['key']] = $v;
                        if ($v !== '') $empty = false;
                    }
                }
                if (!$empty) $clean[] = $obj;
            }
        }
        $content[$rep['key']] = $clean;
    }

    db()->prepare('UPDATE emd_blocks SET content = ? WHERE id = ?')
        ->execute([json_encode($content, JSON_UNESCAPED_UNICODE), $id]);

    redirect('block-edit.php?id=' . $id . '&saved=1');
}

$content = block_data($block);
$brand   = setting('brand', ['logo' => 'assets/logo.png']);
$logo    = '../' . ($brand['logo'] ?? 'assets/logo.png');

/* ---------- field renderers ---------- */
function ed_input($name, $value, $tag = 'input', $ltr = false)
{
    $n = esc($name);
    $v = esc($value);
    $d = $ltr ? ' dir="ltr"' : '';
    if ($tag === 'textarea') {
        return "<textarea name=\"$n\" rows=\"3\"$d>$v</textarea>";
    }
    return "<input type=\"text\" name=\"$n\" value=\"$v\"$d>";
}

function ed_field($field, $src, $prefix)
{
    $kind = $field['kind'];
    if ($kind === 'bil' || $kind === 'bil_area') {
        $tag = $kind === 'bil_area' ? 'textarea' : 'input';
        echo '<div class="ed-field"><label>' . esc($field['label']) . '</label><div class="ed-bil">';
        echo '<div><span class="ed-sub">عربي</span>' . ed_input($prefix . '[' . $field['ar'] . ']', $src[$field['ar']] ?? '', $tag) . '</div>';
        echo '<div><span class="ed-sub">English</span>' . ed_input($prefix . '[' . $field['en'] . ']', $src[$field['en']] ?? '', $tag, true) . '</div>';
        echo '</div></div>';
    } elseif ($kind === 'icon') {
        $val = $src[$field['key']] ?? '';
        echo '<div class="ed-field"><label>' . esc($field['label']) . '</label><select name="' . esc($prefix . '[' . $field['key'] . ']') . '">';
        foreach (icon_options() as $k => $lbl) {
            echo '<option value="' . esc($k) . '"' . ($k === $val ? ' selected' : '') . '>' . esc($lbl) . '</option>';
        }
        echo '</select></div>';
    } else {
        $tag = $kind === 'textarea' ? 'textarea' : 'input';
        echo '<div class="ed-field"><label>' . esc($field['label']) . '</label>' . ed_input($prefix . '[' . $field['key'] . ']', $src[$field['key']] ?? '', $tag) . '</div>';
    }
}

function ed_rep_item($rep, $item, $idx)
{
    $prefix = 'rep[' . $rep['key'] . '][' . $idx . ']';
    echo '<div class="rep-item" data-item>';
    echo '<button type="button" class="rep-item__del" data-remove title="حذف العنصر">&times;</button>';
    echo '<div class="rep-item__fields">';
    foreach ($rep['subfields'] as $sf) {
        ed_field($sf, $item, $prefix);
    }
    echo '</div></div>';
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title>تعديل: <?= esc(block_label($type)) ?> — emdatra</title>
<link rel="icon" type="image/png" href="<?= esc($logo) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="topbar">
  <div class="topbar__inner">
    <div class="topbar__brand">
      <img src="<?= esc($logo) ?>" alt="emdatra">
      <div><b>emdatra</b><span>تعديل قسم</span></div>
    </div>
    <div class="topbar__actions">
      <a class="btn btn--ghost btn--sm" href="index.php">رجوع للوحة التحكم</a>
    </div>
  </div>
</div>

<div class="wrap">
  <div class="page-head">
    <div>
      <h1>تعديل: <?= esc(block_label($type)) ?></h1>
      <p>عدّل النصوص بالعربية والإنجليزية. لا تترك حقلًا فارغًا إلا إن أردت إخفاء النص.</p>
    </div>
  </div>

  <?php if ($saved): ?>
    <div class="msg msg--ok">تم حفظ التغييرات بنجاح.</div>
  <?php endif; ?>

  <?php if ($type === 'contact'): ?>
    <div class="hint" style="margin-top:0;margin-bottom:18px">
      بيانات التواصل (الهاتف، البريد، العنوان، ساعات العمل) تُعدَّل من صفحة <strong>الإعدادات العامة</strong> (قريبًا)، لأنها مشتركة مع تذييل الموقع.
    </div>
  <?php endif; ?>

  <form method="post" class="editor">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$id ?>">

    <?php foreach ($schema['fields'] as $field) ed_field($field, $content, 'content'); ?>

    <?php foreach ($schema['repeaters'] as $rep):
        $items = isset($content[$rep['key']]) && is_array($content[$rep['key']]) ? $content[$rep['key']] : [];
    ?>
      <div class="rep" data-repeater>
        <div class="rep__head">
          <h3><?= esc($rep['label']) ?></h3>
          <button type="button" class="btn btn--ghost btn--sm" data-add="<?= esc($rep['key']) ?>">+ إضافة <?= esc($rep['item_label']) ?></button>
        </div>
        <div class="rep__list" data-list="<?= esc($rep['key']) ?>">
          <?php foreach ($items as $i => $item) ed_rep_item($rep, $item, $i); ?>
        </div>
        <template id="tpl-<?= esc($rep['key']) ?>"><?php ed_rep_item($rep, [], '__i__'); ?></template>
      </div>
    <?php endforeach; ?>

    <div class="editor__bar">
      <button type="submit" class="btn">حفظ التغييرات</button>
      <a class="btn btn--ghost" href="index.php">إلغاء</a>
    </div>
  </form>
</div>

<script src="../js/admin.js"></script>
</body>
</html>
