<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/catalog.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    if ($action === 'delete' && $id) product_delete($id);
    elseif ($action === 'toggle' && $id) product_toggle_active($id);
    redirect('catalog.php');
}

$products = [];
try { $products = products_all(); } catch (Throwable $e) {}
$total = count($products);

$PAGE_TITLE = 'الكتالوج';
$ACTIVE     = 'catalog';
require __DIR__ . '/_header.php';
?>

<div class="intro" style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
  <p style="margin:0">أضف السلع والمنتجات التي تتاجر بها لتظهر في صفحة الكتالوج على الموقع.</p>
  <a class="btn" href="product-edit.php"><?= ui_icon('plus', 17) ?> منتج جديد</a>
</div>

<?php if ($total === 0): ?>
  <div class="empty">
    <div class="empty__icon"><?= ui_icon('package', 30) ?></div>
    <h2>لا توجد منتجات بعد</h2>
    <p>أضف أول منتج ليظهر في كتالوج موقعك، مع صورته وبلد منشئه والحدّ الأدنى للطلب.</p>
    <a class="btn" href="product-edit.php"><?= ui_icon('plus', 18) ?> إضافة منتج</a>
  </div>
<?php else: ?>
  <div class="sections">
    <?php foreach ($products as $p):
      $on = (int) $p['is_active'] === 1;
      $meta = array_filter([$p['category_ar'] ?? '', $p['origin'] ?? '']);
    ?>
      <div class="section-card <?= $on ? '' : 'is-hidden' ?>">
        <div class="section-card__icon" style="<?= !empty($p['image']) ? 'padding:0;overflow:hidden' : '' ?>">
          <?php if (!empty($p['image'])): ?><img src="../<?= esc($p['image']) ?>" alt="" style="width:100%;height:100%;object-fit:cover"><?php else: ?><?= ui_icon('package', 24) ?><?php endif; ?>
        </div>
        <div class="section-card__body">
          <div class="section-card__title"><?= esc($p['name_ar']) ?></div>
          <div class="section-card__meta"><?= esc(implode('  ·  ', $meta)) ?></div>
        </div>
        <div class="section-card__actions">
          <span class="pill <?= $on ? 'pill--on' : 'pill--off' ?>"><span class="pill__dot"></span><?= $on ? 'ظاهر' : 'مخفي' ?></span>
          <a class="btn btn--ghost btn--sm" href="product-edit.php?id=<?= (int) $p['id'] ?>"><?= ui_icon('edit', 15) ?> تعديل</a>
          <form method="post" class="inline">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="toggle">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button type="submit" class="iconbtn" title="<?= $on ? 'إخفاء' : 'إظهار' ?>"><?= ui_icon($on ? 'eye-off' : 'eye', 17) ?></button>
          </form>
          <form method="post" class="inline" onsubmit="return confirm('حذف هذا المنتج نهائيًا؟');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button type="submit" class="iconbtn iconbtn--danger" title="حذف"><?= ui_icon('trash', 17) ?></button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
