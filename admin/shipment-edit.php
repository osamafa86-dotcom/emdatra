<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/shipments.php';
require_login();

function sh_select($name, $options, $current)
{
    $h = '<select name="' . esc($name) . '">';
    foreach ($options as $k => $lbl) {
        $h .= '<option value="' . esc($k) . '"' . ($k === $current ? ' selected' : '') . '>' . esc($lbl) . '</option>';
    }
    return $h . '</select>';
}
function sh_status_options()
{
    $o = [];
    foreach (shipment_statuses() as $k => $v) $o[$k] = $v['ar'];
    return $o;
}
function sh_mode_options()
{
    $o = [];
    foreach (shipment_modes() as $k => $v) $o[$k] = $v['ar'];
    return $o;
}

$id     = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$ship   = $id ? shipment_by_id($id) : null;
if ($id && !$ship) redirect('shipments.php');
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete' && $ship) {
        shipment_delete($id);
        redirect('shipments.php');
    }

    if ($action === 'add_event' && $ship) {
        $status = $_POST['ev_status'] ?? '';
        if (!shipment_valid_status($status)) $status = $ship['status'];
        $at = trim($_POST['ev_at'] ?? '');
        $at = $at !== '' ? date('Y-m-d H:i:s', strtotime($at)) : date('Y-m-d H:i:s');
        shipment_add_event($id, $status, trim($_POST['ev_location'] ?? ''), trim($_POST['ev_note'] ?? ''), $at);
        redirect('shipment-edit.php?id=' . $id . '&saved=1');
    }

    if ($action === 'del_event' && $ship) {
        shipment_delete_event((int) ($_POST['ev_id'] ?? 0));
        redirect('shipment-edit.php?id=' . $id);
    }

    // save (create or update)
    $mode = $_POST['mode'] ?? 'sea';
    if (!array_key_exists($mode, shipment_modes())) $mode = 'sea';
    $status = $_POST['status'] ?? 'booked';
    if (!shipment_valid_status($status)) $status = 'booked';

    $data = [
        'tracking_no'   => shipment_normalize_tracking($_POST['tracking_no'] ?? ''),
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'origin'        => trim($_POST['origin'] ?? ''),
        'destination'   => trim($_POST['destination'] ?? ''),
        'description'   => trim($_POST['description'] ?? ''),
        'mode'          => $mode,
        'status'        => $status,
        'eta'           => trim($_POST['eta'] ?? ''),
    ];

    if ($data['tracking_no'] === '') $errors[] = 'رقم التتبّع مطلوب.';
    elseif (shipment_tracking_exists($data['tracking_no'], $id)) $errors[] = 'رقم التتبّع مستخدم لشحنة أخرى.';

    if (!$errors) {
        if ($ship) {
            shipment_update($id, $data);
            redirect('shipment-edit.php?id=' . $id . '&saved=1');
        }
        $newId = shipment_create($data);
        shipment_add_event($newId, $data['status'], $data['origin'], '', date('Y-m-d H:i:s'));
        redirect('shipment-edit.php?id=' . $newId . '&saved=1');
    }
    $ship = array_merge(['id' => $id], $data); // repopulate form on error
}

$saved  = isset($_GET['saved']);
$isNew  = !$id;
$events = $id ? shipment_events($id) : [];

$v = function ($k) use ($ship) { return $ship[$k] ?? ''; };
$trackingDefault = $ship ? $v('tracking_no') : shipment_suggest_tracking();

$PAGE_TITLE = $isNew ? 'شحنة جديدة' : 'تعديل الشحنة';
$ACTIVE     = 'shipments';
require __DIR__ . '/_header.php';
?>

<a href="shipments.php" class="conv-back"><?= ui_icon('back', 16) ?> كل الشحنات</a>

<?php if ($saved): ?><div class="msg msg--ok"><?= ui_icon('save', 18) ?> تم الحفظ بنجاح.</div><?php endif; ?>
<?php foreach ($errors as $er): ?><div class="msg msg--err"><?= ui_icon('close', 18) ?> <?= esc($er) ?></div><?php endforeach; ?>

<form method="post" class="editor">
  <?= csrf_field() ?>
  <input type="hidden" name="id" value="<?= (int) $id ?>">

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('truck', 20) ?></span><div><h3>بيانات الشحنة</h3><span class="sub">يراها العميل عند التتبّع (عدا اسم العميل)</span></div></div>
    <div class="ed-field"><label>رقم التتبّع</label><input type="text" name="tracking_no" value="<?= esc($trackingDefault) ?>" dir="ltr" required></div>
    <div class="ed-field"><label>اسم العميل (داخلي)</label><input type="text" name="customer_name" value="<?= esc($v('customer_name')) ?>"></div>
    <div class="ed-bil">
      <div class="ed-field" style="margin:0"><label>بلد/مدينة المصدر</label><input type="text" name="origin" value="<?= esc($v('origin')) ?>"></div>
      <div class="ed-field" style="margin:0"><label>بلد/مدينة الوجهة</label><input type="text" name="destination" value="<?= esc($v('destination')) ?>"></div>
    </div>
    <div class="ed-field"><label>وصف البضاعة</label><input type="text" name="description" value="<?= esc($v('description')) ?>"></div>
    <div class="ed-bil">
      <div class="ed-field" style="margin:0"><label>وسيلة الشحن</label><?= sh_select('mode', sh_mode_options(), $v('mode') ?: 'sea') ?></div>
      <div class="ed-field" style="margin:0"><label>الحالة الحالية</label><?= sh_select('status', sh_status_options(), $v('status') ?: 'booked') ?></div>
    </div>
    <div class="ed-field"><label>الوصول المتوقّع (ETA)</label><input type="date" name="eta" value="<?= esc($v('eta')) ?>" dir="ltr"></div>
  </div>

  <div class="editor__bar">
    <span class="spacer"><?= $isNew ? 'بعد الحفظ يمكنك إضافة تحديثات المسار' : '' ?></span>
    <?php if (!$isNew): ?>
      <button type="submit" class="btn btn--ghost" name="action" value="delete" formnovalidate onclick="return confirm('حذف هذه الشحنة نهائيًا؟');"><?= ui_icon('trash', 16) ?> حذف</button>
    <?php endif; ?>
    <button type="submit" class="btn" name="action" value="save"><?= ui_icon('save', 17) ?> حفظ</button>
  </div>
</form>

<?php if (!$isNew): ?>
  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('pin', 19) ?></span><div><h3>تحديثات المسار</h3><span class="sub">كل تحديث يصبح الحالة الحالية ويظهر للعميل</span></div></div>

    <form method="post" class="sh-evform">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int) $id ?>">
      <input type="hidden" name="action" value="add_event">
      <div class="ed-bil">
        <div class="ed-field" style="margin:0"><label>الحالة</label><?= sh_select('ev_status', sh_status_options(), $ship['status'] ?? 'in_transit') ?></div>
        <div class="ed-field" style="margin:0"><label>الموقع</label><input type="text" name="ev_location" placeholder="مثال: ميناء جدة"></div>
      </div>
      <div class="ed-field"><label>ملاحظة (تظهر للعميل)</label><input type="text" name="ev_note" placeholder="اختياري"></div>
      <div class="ed-field"><label>التاريخ والوقت</label><input type="datetime-local" name="ev_at" dir="ltr"></div>
      <button type="submit" class="btn btn--sm"><?= ui_icon('plus', 15) ?> إضافة تحديث</button>
    </form>

    <?php if ($events): ?>
      <ul class="sh-ev-list">
        <?php foreach ($events as $i => $e): ?>
          <li class="sh-ev <?= $i === 0 ? 'is-latest' : '' ?>">
            <span class="sh-ev__dot"></span>
            <div class="sh-ev__body">
              <b><?= esc(shipment_status_label($e['status'])) ?></b>
              <span class="sh-ev__time" dir="ltr"><?= esc(date('Y/m/d H:i', strtotime($e['event_at']))) ?></span>
              <?php $sub = array_filter([$e['location'], $e['note']]); if ($sub): ?>
                <div class="sh-ev__sub"><?= esc(implode(' · ', $sub)) ?></div>
              <?php endif; ?>
            </div>
            <form method="post" class="inline" onsubmit="return confirm('حذف هذا التحديث؟');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $id ?>">
              <input type="hidden" name="action" value="del_event">
              <input type="hidden" name="ev_id" value="<?= (int) $e['id'] ?>">
              <button type="submit" class="iconbtn iconbtn--danger" title="حذف"><?= ui_icon('trash', 15) ?></button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p class="hint">لا توجد تحديثات بعد. أضف أول تحديث للمسار.</p>
    <?php endif; ?>
  </div>
<?php endif; ?>

<?php require __DIR__ . '/_footer.php'; ?>
