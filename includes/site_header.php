<?php
/**
 * Shared site shell — opening markup + header.
 * A page may set before including: $pageActive, $pageTitleAr, $pageTitleEn, $pageDescAr.
 */
$isHome  = ($pageActive ?? '') === 'home';
$titleAr = $pageTitleAr ?? ($seo['title_ar'] ?? 'إمداترا | حلول الاستيراد والتصدير');
$titleEn = $pageTitleEn ?? ($seo['title_en'] ?? 'emdatra | Import & Export Solutions');
$descAr  = $pageDescAr ?? ($seo['desc_ar'] ?? '');
$logoHref = $isHome ? '#home' : 'index.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= esc($titleAr) ?></title>
  <meta name="description" content="<?= esc($descAr) ?>" />
  <meta name="theme-color" content="#14171C" />

  <link rel="icon" type="image/png" href="<?= esc($logo) ?>" />
  <link rel="apple-touch-icon" href="<?= esc($logo) ?>" />

  <meta property="og:title" content="<?= esc($titleAr) ?>" />
  <meta property="og:description" content="<?= esc($descAr) ?>" />
  <meta property="og:type" content="website" />

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;900&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/style.css?v=<?= @filemtime(__DIR__ . '/../css/style.css') ?: '1' ?>" />
  <link rel="stylesheet" href="css/chat.css?v=<?= @filemtime(__DIR__ . '/../css/chat.css') ?: '1' ?>" />
</head>
<body>

  <header class="header" id="header">
    <div class="container header__inner">
      <a href="<?= esc($logoHref) ?>" class="logo" aria-label="<?= esc($brand['name'] ?? 'emdatra') ?>">
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
          <a href="<?= esc(nav_href($item['href'] ?? '#', $isHome)) ?>" class="nav__link <?= ($isHome && $i === 0) ? 'is-active' : '' ?>"
             data-ar="<?= esc($item['ar'] ?? '') ?>" data-en="<?= esc($item['en'] ?? '') ?>"><?= esc($item['ar'] ?? '') ?></a>
        <?php endforeach; ?>
      </nav>

      <div class="header__actions">
        <button class="lang-toggle" id="langToggle" type="button" aria-label="تبديل اللغة">
          <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.6 2.7 3.9 6 3.9 9s-1.3 6.3-3.9 9c-2.6-2.7-3.9-6-3.9-9S9.4 5.7 12 3Z"/></svg>
          <span id="langLabel">EN</span>
        </button>
        <a href="<?= esc(nav_href($headerCta['href'] ?? '#contact', $isHome)) ?>" class="btn btn--primary btn--sm header__cta" data-quote
           data-ar="<?= esc($headerCta['ar'] ?? '') ?>" data-en="<?= esc($headerCta['en'] ?? '') ?>"><?= esc($headerCta['ar'] ?? '') ?></a>
        <button class="menu-btn" id="menuBtn" type="button" aria-label="فتح القائمة" aria-expanded="false">
          <span></span><span></span><span></span>
        </button>
      </div>
    </div>
  </header>
  <div class="nav-backdrop" id="navBackdrop"></div>
