<?php
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/icons.php';
require_login();

/* ---------- field renderers ---------- */
function sf_input($n, $v, $tag = 'input', $ltr = false)
{
    $d = $ltr ? ' dir="ltr"' : '';
    if ($tag === 'textarea') return '<textarea name="' . esc($n) . '" rows="3"' . $d . '>' . esc($v) . '</textarea>';
    return '<input type="text" name="' . esc($n) . '" value="' . esc($v) . '"' . $d . '>';
}
function sf_text($label, $n, $v, $ltr = false)
{
    echo '<div class="ed-field"><label>' . esc($label) . '</label>' . sf_input($n, $v, 'input', $ltr) . '</div>';
}
function sf_bil($label, $nAr, $vAr, $nEn, $vEn, $area = false)
{
    $tag = $area ? 'textarea' : 'input';
    echo '<div class="ed-field"><label>' . esc($label) . '</label><div class="ed-bil">';
    echo '<div><span class="ed-sub">عربي</span>' . sf_input($nAr, $vAr, $tag) . '</div>';
    echo '<div><span class="ed-sub en">English</span>' . sf_input($nEn, $vEn, $tag, true) . '</div>';
    echo '</div></div>';
}
function handle_logo_upload($file)
{
    if (!empty($file['error']) || $file['size'] <= 0 || $file['size'] > 2 * 1024 * 1024) return null;
    $info = @getimagesize($file['tmp_name']);
    if (!$info) return null;
    $map = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!isset($map[$info[2]])) return null;
    $dir = __DIR__ . '/../assets';
    if (!is_dir($dir) || !is_writable($dir)) return null;
    $name = 'logo-' . bin2hex(random_bytes(6)) . '.' . $map[$info[2]];
    if (!@move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) return null;
    return 'assets/' . $name;
}

$flash = '';
$warn  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $p = $_POST;

    $brand = setting('brand', []);
    $brand['name']   = trim($p['brand']['name'] ?? ($brand['name'] ?? 'emdatra'));
    $brand['tag_ar'] = trim($p['brand']['tag_ar'] ?? '');
    $brand['tag_en'] = trim($p['brand']['tag_en'] ?? '');
    if (!empty($_FILES['logo']['name'])) {
        $up = handle_logo_upload($_FILES['logo']);
        if ($up) $brand['logo'] = $up;
        else $warn = 'تعذّر رفع الشعار — تأكد أنه صورة PNG/JPG/WebP أقل من 2MB. تم حفظ بقية التغييرات.';
    }
    if (empty($brand['logo'])) $brand['logo'] = 'assets/logo.png';
    save_setting('brand', $brand);

    save_setting('seo', [
        'title_ar' => trim($p['seo']['title_ar'] ?? ''), 'title_en' => trim($p['seo']['title_en'] ?? ''),
        'desc_ar'  => trim($p['seo']['desc_ar'] ?? ''),  'desc_en'  => trim($p['seo']['desc_en'] ?? ''),
    ]);

    save_setting('contact_info', [
        'phone'      => trim($p['contact']['phone'] ?? ''),      'email'      => trim($p['contact']['email'] ?? ''),
        'address_ar' => trim($p['contact']['address_ar'] ?? ''), 'address_en' => trim($p['contact']['address_en'] ?? ''),
        'hours_ar'   => trim($p['contact']['hours_ar'] ?? ''),   'hours_en'   => trim($p['contact']['hours_en'] ?? ''),
    ]);

    save_setting('header_cta', [
        'ar'   => trim($p['cta']['ar'] ?? ''), 'en' => trim($p['cta']['en'] ?? ''),
        'href' => trim($p['cta']['href'] ?? '') !== '' ? trim($p['cta']['href']) : '#contact',
    ]);

    save_setting('socials', [
        'instagram' => trim($p['socials']['instagram'] ?? '#'), 'x'        => trim($p['socials']['x'] ?? '#'),
        'linkedin'  => trim($p['socials']['linkedin'] ?? '#'),  'facebook' => trim($p['socials']['facebook'] ?? '#'),
    ]);

    save_setting('footer', [
        'desc_ar' => trim($p['footer']['desc_ar'] ?? ''), 'desc_en' => trim($p['footer']['desc_en'] ?? ''),
        'copy_ar' => trim($p['footer']['copy_ar'] ?? ''), 'copy_en' => trim($p['footer']['copy_en'] ?? ''),
    ]);

    $nav = [];
    foreach (($p['nav'] ?? []) as $row) {
        if (!is_array($row)) continue;
        $ar = trim($row['ar'] ?? ''); $en = trim($row['en'] ?? ''); $href = trim($row['href'] ?? '');
        if ($ar === '' && $en === '') continue;
        $nav[] = ['ar' => $ar, 'en' => $en, 'href' => $href !== '' ? $href : '#'];
    }
    save_setting('nav', $nav);

    if ($warn) { $_SESSION['set_warn'] = $warn; }
    redirect('settings.php?saved=1');
}

$saved = isset($_GET['saved']);
if (!empty($_SESSION['set_warn'])) { $warn = $_SESSION['set_warn']; unset($_SESSION['set_warn']); }

$brand = setting('brand', []);
$seo   = setting('seo', []);
$ci    = setting('contact_info', []);
$cta   = setting('header_cta', []);
$soc   = setting('socials', []);
$foot  = setting('footer', []);
$nav   = setting('nav', []);

$navItem = function ($i, $row) {
    echo '<div class="rep-item" data-item>';
    echo '<button type="button" class="rep-item__del" data-remove title="حذف الرابط">' . ui_icon('trash', 16) . '</button>';
    echo '<div class="rep-item__fields">';
    sf_text('العربية', "nav[$i][ar]", $row['ar'] ?? '');
    sf_text('English', "nav[$i][en]", $row['en'] ?? '', true);
    sf_text('الرابط (مثل ‎#about‎)', "nav[$i][href]", $row['href'] ?? '#', true);
    echo '</div></div>';
};

$PAGE_TITLE = 'الإعدادات العامة';
$ACTIVE     = 'settings';
require __DIR__ . '/_header.php';
?>

<?php if ($saved): ?><div class="msg msg--ok"><?= ui_icon('save', 18) ?> تم حفظ الإعدادات بنجاح.</div><?php endif; ?>
<?php if ($warn): ?><div class="msg msg--err"><?= ui_icon('close', 18) ?> <?= esc($warn) ?></div><?php endif; ?>

<div class="intro"><p>تتحكّم هذه الإعدادات بعناصر الموقع المشتركة: الشعار، التواصل، القائمة، التذييل، وبيانات محركات البحث.</p></div>

<form method="post" class="editor" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= block_type_icon('hero', 20) ?></span><div><h3>الهوية والشعار</h3><span class="sub">Brand</span></div></div>
    <div class="ed-field">
      <label>شعار الموقع</label>
      <div class="set-logo">
        <img src="../<?= esc($brand['logo'] ?? 'assets/logo.png') ?>" alt="" class="set-logo__img">
        <div class="set-logo__up">
          <input type="file" name="logo" accept="image/png,image/jpeg,image/webp">
          <span class="ed-sub">PNG / JPG / WebP، أقل من 2MB. اتركه فارغًا للإبقاء على الشعار الحالي.</span>
        </div>
      </div>
    </div>
    <?php sf_text('اسم الموقع', 'brand[name]', $brand['name'] ?? 'emdatra'); ?>
    <?php sf_bil('الوصف تحت الاسم', 'brand[tag_ar]', $brand['tag_ar'] ?? '', 'brand[tag_en]', $brand['tag_en'] ?? ''); ?>
  </div>

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('external', 19) ?></span><div><h3>بيانات محركات البحث (SEO)</h3><span class="sub">تظهر في نتائج البحث وعند المشاركة</span></div></div>
    <?php sf_bil('عنوان الصفحة', 'seo[title_ar]', $seo['title_ar'] ?? '', 'seo[title_en]', $seo['title_en'] ?? ''); ?>
    <?php sf_bil('وصف الموقع', 'seo[desc_ar]', $seo['desc_ar'] ?? '', 'seo[desc_en]', $seo['desc_en'] ?? '', true); ?>
  </div>

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('mail', 19) ?></span><div><h3>معلومات التواصل</h3><span class="sub">تظهر في قسم التواصل والتذييل</span></div></div>
    <?php sf_text('رقم الهاتف', 'contact[phone]', $ci['phone'] ?? '', true); ?>
    <?php sf_text('البريد الإلكتروني', 'contact[email]', $ci['email'] ?? '', true); ?>
    <?php sf_bil('العنوان', 'contact[address_ar]', $ci['address_ar'] ?? '', 'contact[address_en]', $ci['address_en'] ?? ''); ?>
    <?php sf_bil('ساعات العمل', 'contact[hours_ar]', $ci['hours_ar'] ?? '', 'contact[hours_en]', $ci['hours_en'] ?? ''); ?>
  </div>

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('plus', 19) ?></span><div><h3>زر الهيدر</h3><span class="sub">الزر العلوي في الموقع</span></div></div>
    <?php sf_bil('نص الزر', 'cta[ar]', $cta['ar'] ?? '', 'cta[en]', $cta['en'] ?? ''); ?>
    <?php sf_text('رابط الزر', 'cta[href]', $cta['href'] ?? '#contact', true); ?>
  </div>

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= ui_icon('external', 19) ?></span><div><h3>روابط التواصل الاجتماعي</h3><span class="sub">اتركها # إن لم تتوفر</span></div></div>
    <?php sf_text('Instagram', 'socials[instagram]', $soc['instagram'] ?? '#', true); ?>
    <?php sf_text('X (Twitter)', 'socials[x]', $soc['x'] ?? '#', true); ?>
    <?php sf_text('LinkedIn', 'socials[linkedin]', $soc['linkedin'] ?? '#', true); ?>
    <?php sf_text('Facebook', 'socials[facebook]', $soc['facebook'] ?? '#', true); ?>
  </div>

  <div class="ed-card">
    <div class="ed-card__head"><span class="ic"><?= block_type_icon('cta', 19) ?></span><div><h3>التذييل</h3><span class="sub">Footer</span></div></div>
    <?php sf_bil('وصف التذييل', 'footer[desc_ar]', $foot['desc_ar'] ?? '', 'footer[desc_en]', $foot['desc_en'] ?? '', true); ?>
    <?php sf_bil('حقوق النشر', 'footer[copy_ar]', $foot['copy_ar'] ?? '', 'footer[copy_en]', $foot['copy_en'] ?? ''); ?>
  </div>

  <div class="ed-card rep" data-repeater>
    <div class="ed-card__head">
      <span class="ic"><?= ui_icon('menu', 19) ?></span>
      <div style="flex:1"><h3>قائمة التنقّل</h3><span class="sub">روابط القائمة العلوية</span></div>
      <button type="button" class="btn btn--ghost btn--sm" data-add="nav"><?= ui_icon('plus', 15) ?> إضافة رابط</button>
    </div>
    <div class="rep__list" data-list="nav">
      <?php foreach ($nav as $i => $row) $navItem($i, $row); ?>
    </div>
    <template id="tpl-nav"><?php $navItem('__i__', []); ?></template>
  </div>

  <div class="editor__bar">
    <span class="spacer">تُطبَّق التغييرات على الموقع فورًا بعد الحفظ</span>
    <button type="submit" class="btn"><?= ui_icon('save', 17) ?> حفظ الإعدادات</button>
  </div>
</form>

<?php require __DIR__ . '/_footer.php'; ?>
