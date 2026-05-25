<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/catalog.php';
require_login();

function pe_input($n, $v, $tag = 'input', $ltr = false)
{
    $d = $ltr ? ' dir="ltr"' : '';
    if ($tag === 'textarea') return '<textarea name="' . esc($n) . '" rows="3"' . $d . '>' . esc($v) . '</textarea>';
    return '<input type="text" name="' . esc($n) . '" value="' . esc($v) . '"' . $d . '>';
}
function pe_text($label, $n, $v, $ltr = false)
{
    echo '<div class="ed-field"><label>' . esc($label) . '</label>' . pe_input($n, $v, 'input', $ltr) . '</div>';
}
function pe_bil($label, $nAr, $vAr, $nEn, $vEn, $area = false)
{
    $tag = $area ? 'textarea' : 'input';
    echo '<div class="ed-field"><label>' . esc($label) . '</label><div class="ed-bil">';
    echo '<div><span class="ed-sub">عربي</span>' . pe_input($nAr, $vAr, $tag) . '</div>';
    echo '<div><span class="ed-sub en">English</span>' . pe_input($nEn, $vEn, $tag, true) . '</div>';
    echo '</div></div>';
}
function handle_product_image($file)
{
    if (!empty($file['error']) || $file['size'] <= 0 || $file['size'] > 3 * 1024 * 1024) return null;
    $info = @getimagesize($file['tmp_name']);
    if (!$info) return null;
    $map = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!isset($map[$info[2]])) return null;
    $dir = __DIR__ . '/../assets/products';
    if (!is_dir($dir)) @mkdir($dir, 0775, true);
    if (!is_dir($dir) || !is_writable($dir)) return null;
    $name = 'p-' . bin2hex(random_bytes(6)) . '.' . $map[$info[2]];
    if (!@move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) return null;
    return 'assets/products/' . $name;
}

$id   = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$prod = $id ? product_by_id($id) : null;
if ($id && !$prod) redirect('catalog.php');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'delete' && $prod) {
        product_delete($id);
        redirect('catalog.php');
    }

    $data = [
        'name_ar'     => trim($_POST['name_ar'] ?? ''),
        'name_en'     => trim($_POST['name_en'] ?? ''),
        'category_ar' => trim($_POST['category_ar'] ?? ''),
        'category_en' => trim($_POST['category_en'] ?? ''),
        'origin'      => trim($_POST['origin'] ?? ''),
        'moq'         => trim($_POST['moq'] ?? ''),
        'unit_ar'     => trim($_POST['unit_ar'] ?? ''),
        'unit_en'     => trim($_POST['unit_en'] ?? ''),
        'desc_ar'     => trim($_POST['desc_ar'] ?? ''),
        'desc_en'     => trim($_POST['desc_en'] ?? ''),
        'image'       => $prod['image'] ?? '',
        'is_active'   => isset($_POST['is_active']) ? 1 : 0,
        'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
    ];
    if (!empty($_FILES['image']['name'])) {
        $up = handle_product_image($_FILES['image']);
        if ($up) $data['image'] = $up;
        else $errors[] = 'تعذّر رفع الصورة (PNG/JPG/WebP أقل من 3MB).';
    }
    if ($data['name_ar'] === '') $errors[] = 'اسم المنتج (عربي) مطلوب.';

    if (!$errors) {
        if ($prod) { product_update($id, $data); redirect('product-edit.php?id=' . $id . '&saved=1'); }
        $newId = product_create($data);
        redirect('product-edit.php?id=' . $newId . '&saved=1');
    }
    $prod = array_merge(['id' => $id], $data);
}

$saved = isset($_GET['saved']);
$isNew = !$id;
$g = function ($k) use ($prod) { return $prod[$k] ?? ''; };

$PAGE_TITLE = $isNew ? 'منتج جديد' : 'تعديل المنتج';
$ACTIVE     = 'catalog';
require __DIR__ . '/_header.php';
?>

<a href="catalog.php" class="conv-back"><?= ui_icon('back', 16) ?> كل المنتجات</a>

<?php if ($saved): ?><div class="msg msg--ok"><?= ui_icon('save', 18) ?> تم الحفظ بنجاح.</div><?php endif; ?>
<?php foreach ($errors as $er): ?><div class="msg msg--err"><?= ui_icon('close', 18) ?> <?= esc($er) ?></div><?php endforeach; ?>

<form method="post" class="editor" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('package', 20) ?></span><div><h3>بيانات المنتج</h3><span class="sub">تظهر في كتالوج الموقع</span></div></div>

    <div class="ed-field">
      <label>صورة المنتج</label>
      <div class="set-logo">
        <img src="<?= !empty($g('image')) ? '../' . esc($g('image')) : 'data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22/>' ?>" alt="" class="set-logo__img" style="<?= empty($g('image')) ? 'opacity:.3' : '' ?>">
        <div class="set-logo__up">
          <input type="file" name="image" accept="image/png,image/jpeg,image/webp">
          <span class="ed-sub">PNG / JPG / WebP، أقل من 3MB. اتركه فارغًا للإبقاء على الصورة الحالية.</span>
        </div>
      </div>
    </div>

    <?php pe_bil('اسم المنتج', 'name_ar', $g('name_ar'), 'name_en', $g('name_en')); ?>
    <?php pe_bil('الفئة', 'category_ar', $g('category_ar'), 'category_en', $g('category_en')); ?>
    <?php pe_bil('وصف مختصر', 'desc_ar', $g('desc_ar'), 'desc_en', $g('desc_en'), true); ?>
    <?php pe_text('بلد المنشأ', 'origin', $g('origin')); ?>
    <div class="ed-bil">
      <div class="ed-field" style="margin:0"><label>أقل كمية للطلب</label><?= pe_input('moq', $g('moq')) ?></div>
      <div class="ed-field" style="margin:0"><label>الوحدة (عربي / EN)</label><div class="ed-bil"><?= pe_input('unit_ar', $g('unit_ar')) ?><?= pe_input('unit_en', $g('unit_en'), 'input', true) ?></div></div>
    </div>
    <div class="ed-bil">
      <div class="ed-field" style="margin:0"><label>ترتيب العرض</label><input type="number" name="sort_order" value="<?= (int) ($g('sort_order') ?: 0) ?>" dir="ltr"></div>
      <div class="ed-field" style="margin:0;display:flex;align-items:center;gap:9px"><input type="checkbox" name="is_active" value="1" id="pActive" <?= ($isNew || (int) $g('is_active') === 1) ? 'checked' : '' ?> style="width:auto"><label for="pActive" style="margin:0">إظهار في الموقع</label></div>
    </div>
  </div>

  <div class="editor__bar">
    <span class="spacer"></span>
    <?php if (!$isNew): ?>
      <button type="submit" class="btn btn--ghost" name="action" value="delete" formnovalidate onclick="return confirm('حذف هذا المنتج نهائيًا؟');"><?= ui_icon('trash', 16) ?> حذف</button>
    <?php endif; ?>
    <button type="submit" class="btn" name="action" value="save"><?= ui_icon('save', 17) ?> حفظ</button>
  </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
