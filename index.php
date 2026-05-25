<?php
/**
 * Database-driven public site (live homepage).
 * Renders global settings + visible blocks using the existing design.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/icons.php';

$blocks      = get_blocks('home', true);
$brand       = setting('brand', ['name' => 'emdatra', 'tag_ar' => 'للاستيراد والتصدير', 'tag_en' => 'Import & Export', 'logo' => 'assets/logo.png']);
$nav         = setting('nav', []);
$headerCta   = setting('header_cta', ['ar' => 'اطلب عرض سعر', 'en' => 'Get a Quote', 'href' => '#contact']);
$contactInfo = setting('contact_info', []);
$socials     = setting('socials', []);
$footerS     = setting('footer', []);
$seo         = setting('seo', []);
$logo        = $brand['logo'] ?? 'assets/logo.png';

$servicesItems = [];
foreach ($blocks as $b) {
    if ($b['type'] === 'services') {
        $sd = block_data($b);
        $servicesItems = $sd['items'] ?? [];
        break;
    }
}
$types = block_types();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= esc($seo['title_ar'] ?? 'إمداترا | حلول الاستيراد والتصدير') ?></title>
  <meta name="description" content="<?= esc($seo['desc_ar'] ?? '') ?>" />
  <meta name="theme-color" content="#14171C" />

  <link rel="icon" type="image/png" href="<?= esc($logo) ?>" />
  <link rel="apple-touch-icon" href="<?= esc($logo) ?>" />

  <meta property="og:title" content="<?= esc($seo['title_ar'] ?? 'إمداترا') ?>" />
  <meta property="og:description" content="<?= esc($seo['desc_ar'] ?? '') ?>" />
  <meta property="og:type" content="website" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__ . '/css/style.css') ?: '1' ?>" />
  <link rel="stylesheet" href="css/chat.css?v=<?= @filemtime(__DIR__ . '/css/chat.css') ?: '1' ?>" />
</head>
<body>

  <header class="header" id="header">
    <div class="container header__inner">
      <a href="#home" class="logo" aria-label="<?= esc($brand['name'] ?? 'emdatra') ?>">
        <span class="logo__mark" aria-hidden="true">
          <img src="<?= esc($logo) ?>" alt="<?= esc($brand['name'] ?? 'emdatra') ?>" class="logo__img" />
        </span>
        <span class="logo__text">
          <span class="logo__name"><?= esc($brand['name'] ?? 'emdatra') ?></span>
          <span class="logo__tag" <?= bil_attrs($brand, 'tag') ?>><?= bil_text($brand, 'tag') ?></span>
        </span>
      </a>

      <nav class="nav" id="nav" aria-label="القائمة الرئيسية">
        <?php foreach ($nav as $i => $item): ?>
          <a href="<?= esc($item['href'] ?? '#') ?>" class="nav__link <?= $i === 0 ? 'is-active' : '' ?>"
             data-ar="<?= esc($item['ar'] ?? '') ?>" data-en="<?= esc($item['en'] ?? '') ?>"><?= esc($item['ar'] ?? '') ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="header__actions">
        <button class="lang-toggle" id="langToggle" type="button" aria-label="تبديل اللغة">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.6 2.7 3.9 6 3.9 9s-1.3 6.3-3.9 9c-2.6-2.7-3.9-6-3.9-9S9.4 5.7 12 3Z"/></svg>
          <span id="langLabel">EN</span>
        </button>
        <a href="<?= esc($headerCta['href'] ?? '#contact') ?>" class="btn btn--primary btn--sm header__cta"
           data-ar="<?= esc($headerCta['ar'] ?? '') ?>" data-en="<?= esc($headerCta['en'] ?? '') ?>"><?= esc($headerCta['ar'] ?? '') ?></a>
        <button class="menu-btn" id="menuBtn" type="button" aria-label="فتح القائمة" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>
  <div class="nav-backdrop" id="navBackdrop"></div>

  <main>
    <?php
    foreach ($blocks as $b) {
        if (!isset($types[$b['type']])) continue;
        $tpl = __DIR__ . '/templates/blocks/' . $b['type'] . '.php';
        if (is_file($tpl)) {
            $d = block_data($b);
            include $tpl;
        }
    }
    ?>
  </main>

  <footer class="footer">
    <div class="container">
      <div class="footer__top">
        <div class="footer__brand">
          <a href="#home" class="logo logo--footer" aria-label="<?= esc($brand['name'] ?? 'emdatra') ?>">
            <span class="logo__mark" aria-hidden="true">
              <img src="<?= esc($logo) ?>" alt="<?= esc($brand['name'] ?? 'emdatra') ?>" class="logo__img" />
            </span>
            <span class="logo__text">
              <span class="logo__name"><?= esc($brand['name'] ?? 'emdatra') ?></span>
              <span class="logo__tag" <?= bil_attrs($brand, 'tag') ?>><?= bil_text($brand, 'tag') ?></span>
            </span>
          </a>
          <p class="footer__desc" <?= bil_attrs($footerS, 'desc') ?>><?= bil_text($footerS, 'desc') ?></p>
          <div class="socials">
            <a href="<?= esc($socials['instagram'] ?? '#') ?>" class="social" aria-label="Instagram"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.1"/></svg></a>
            <a href="<?= esc($socials['x'] ?? '#') ?>" class="social" aria-label="X"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4l16 16M20 4 4 20"/></svg></a>
            <a href="<?= esc($socials['linkedin'] ?? '#') ?>" class="social" aria-label="LinkedIn"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v6h-4v-6a2 2 0 0 0-4 0v6h-4V8h4v1.5"/><rect x="2" y="9" width="4" height="11"/><circle cx="4" cy="4" r="1.6"/></svg></a>
            <a href="<?= esc($socials['facebook'] ?? '#') ?>" class="social" aria-label="Facebook"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 3h-2.5A4.5 4.5 0 0 0 9 7.5V10H6v4h3v7h4v-7h3l1-4h-4V7.5A1.5 1.5 0 0 1 14.5 6H16z"/></svg></a>
          </div>
        </div>

        <nav class="footer__col" aria-label="روابط سريعة">
          <h4 data-ar="روابط سريعة" data-en="Quick Links">روابط سريعة</h4>
          <?php foreach ($nav as $item): ?>
            <a href="<?= esc($item['href'] ?? '#') ?>" data-ar="<?= esc($item['ar'] ?? '') ?>" data-en="<?= esc($item['en'] ?? '') ?>"><?= esc($item['ar'] ?? '') ?></a>
          <?php endforeach; ?>
        </nav>

        <nav class="footer__col" aria-label="خدماتنا">
          <h4 data-ar="خدماتنا" data-en="Services">خدماتنا</h4>
          <?php foreach (array_slice($servicesItems, 0, 4) as $s): ?>
            <a href="#services" data-ar="<?= esc($s['title_ar'] ?? '') ?>" data-en="<?= esc($s['title_en'] ?? '') ?>"><?= esc($s['title_ar'] ?? '') ?></a>
          <?php endforeach; ?>
        </nav>

        <div class="footer__col">
          <h4 data-ar="تواصل معنا" data-en="Contact">تواصل معنا</h4>
          <?php if (!empty($contactInfo['phone'])): ?>
            <a href="tel:<?= esc(preg_replace('/\s+/', '', $contactInfo['phone'])) ?>" dir="ltr"><?= esc($contactInfo['phone']) ?></a>
          <?php endif; ?>
          <?php if (!empty($contactInfo['email'])): ?>
            <a href="mailto:<?= esc($contactInfo['email']) ?>" dir="ltr"><?= esc($contactInfo['email']) ?></a>
          <?php endif; ?>
          <span data-ar="<?= esc($contactInfo['address_ar'] ?? '') ?>" data-en="<?= esc($contactInfo['address_en'] ?? '') ?>"><?= esc($contactInfo['address_ar'] ?? '') ?></span>
        </div>
      </div>

      <div class="footer__bar">
        <p class="footer__copy" <?= bil_attrs($footerS, 'copy') ?>><?= bil_text($footerS, 'copy') ?></p>
        <div class="footer__legal">
          <a href="#" data-ar="سياسة الخصوصية" data-en="Privacy Policy">سياسة الخصوصية</a>
          <a href="#" data-ar="الشروط والأحكام" data-en="Terms &amp; Conditions">الشروط والأحكام</a>
        </div>
      </div>
    </div>
  </footer>

  <a href="#home" class="to-top" id="toTop" aria-label="العودة للأعلى">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 15 6-6 6 6"/></svg>
  </a>

  <script src="js/main.js?v=<?= @filemtime(__DIR__ . '/js/main.js') ?: '1' ?>"></script>
  <script src="js/chat.js?v=<?= @filemtime(__DIR__ . '/js/chat.js') ?: '1' ?>"></script>
</body>
</html>
