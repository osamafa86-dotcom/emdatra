<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/shipments.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'delete' && (int) ($_POST['id'] ?? 0)) {
        shipment_delete((int) $_POST['id']);
    }
    redirect('shipments.php');
}

$shipments = [];
try { $shipments = shipment_all(); } catch (Throwable $e) {}
$total = count($shipments);

$PAGE_TITLE = 'الشحنات';
$ACTIVE     = 'shipments';
require __DIR__ . '/_header.php';
?>

<div class="intro" style="display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap">
  <p style="margin:0">أنشئ الشحنات وحدّث حالتها، ليتتبّعها عملاؤك من الموقع برقم التتبّع.</p>
  <a class="btn" href="shipment-edit.php"><?= ui_icon('plus', 17) ?> شحنة جديدة</a>
</div>

<?php if ($total === 0): ?>
  <div class="empty">
    <div class="empty__icon"><?= ui_icon('truck', 30) ?></div>
    <h2>لا توجد شحنات بعد</h2>
    <p>أضف أول شحنة، وستحصل على رقم تتبّع يستطيع العميل إدخاله في الموقع لمتابعة حالتها ومسارها.</p>
    <a class="btn" href="shipment-edit.php"><?= ui_icon('plus', 18) ?> إضافة شحنة</a>
  </div>
<?php else: ?>
  <div class="sections">
    <?php foreach ($shipments as $s):
      $delivered = $s['status'] === 'delivered';
      $route = trim(($s['origin'] ?? '') . ' ← ' . ($s['destination'] ?? ''), ' ←');
      $meta = array_filter([$s['customer_name'] ?? '', $route, shipment_mode_label($s['mode'])]);
    ?>
      <div class="section-card">
        <div class="section-card__icon"><?= ui_icon('truck', 24) ?></div>
        <div class="section-card__body">
          <div class="section-card__title" dir="ltr" style="text-align:start"><?= esc($s['tracking_no']) ?></div>
          <div class="section-card__meta"><?= esc(implode('  ·  ', $meta)) ?><?= !empty($s['eta']) ? '  ·  ETA ' . esc($s['eta']) : '' ?></div>
        </div>
        <div class="section-card__actions">
          <span class="pill <?= $delivered ? 'pill--on' : 'pill--off' ?>"><span class="pill__dot"></span><?= esc(shipment_status_label($s['status'])) ?></span>
          <a class="btn btn--ghost btn--sm" href="shipment-edit.php?id=<?= (int) $s['id'] ?>"><?= ui_icon('edit', 15) ?> تعديل</a>
          <form method="post" class="inline" onsubmit="return confirm('حذف هذه الشحنة وكل تحديثاتها؟');">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button type="submit" class="iconbtn iconbtn--danger" title="حذف"><?= ui_icon('trash', 17) ?></button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
