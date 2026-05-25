<?php
/** Shared admin shell header. Expects: $PAGE_TITLE (string), $ACTIVE (string). */
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/icons.php';
require_once __DIR__ . '/../includes/chat.php';
require_login();

$ACTIVE     = $ACTIVE ?? 'dashboard';
$PAGE_TITLE = $PAGE_TITLE ?? 'لوحة التحكم';
$brand      = setting('brand', ['logo' => 'assets/logo.png']);
$logo       = '../' . ($brand['logo'] ?? 'assets/logo.png');
$cssv       = @filemtime(__DIR__ . '/../css/admin.css') ?: '1';

$nav = [
    'dashboard' => ['label' => 'الأقسام', 'href' => 'index.php', 'icon' => 'layers'],
    'messages'  => ['label' => 'المحادثات', 'href' => 'messages.php', 'icon' => 'mail', 'badge' => chat_admin_unread_total()],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= esc($PAGE_TITLE) ?> — emdatra</title>
<link rel="icon" type="image/png" href="<?= esc($logo) ?>">
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/admin.css?v=<?= $cssv ?>">
</head>
<body>
<div class="app">

  <aside class="side" id="side">
    <div class="side__brand">
      <img src="<?= esc($logo) ?>" alt="emdatra">
      <div class="side__brand-txt"><b>emdatra</b><span>لوحة التحكم</span></div>
    </div>

    <nav class="side__nav">
      <?php foreach ($nav as $key => $item): ?>
        <a href="<?= esc($item['href']) ?>" class="side__link <?= $ACTIVE === $key ? 'is-active' : '' ?>">
          <span class="side__ic"><?= ui_icon($item['icon'], 19) ?></span>
          <?= esc($item['label']) ?>
          <?php if (!empty($item['badge'])): ?><span class="side__badge"><?= (int) $item['badge'] ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
      <a href="../" target="_blank" rel="noopener" class="side__link">
        <span class="side__ic"><?= ui_icon('external', 19) ?></span>
        معاينة الموقع
      </a>
    </nav>

    <div class="side__foot">
      <div class="side__user">
        <span class="side__avatar"><?= esc(mb_strtoupper(mb_substr($_SESSION['admin_user'] ?? 'A', 0, 1))) ?></span>
        <span class="side__uname"><?= esc($_SESSION['admin_user'] ?? '') ?></span>
      </div>
      <a href="logout.php" class="side__logout" title="تسجيل الخروج"><?= ui_icon('logout', 18) ?></a>
    </div>
  </aside>

  <div class="side-backdrop" id="sideBackdrop"></div>

  <main class="main">
    <header class="main__top">
      <button class="side__toggle" id="sideToggle" type="button" aria-label="القائمة"><?= ui_icon('menu', 22) ?></button>
      <h1 class="main__title"><?= esc($PAGE_TITLE) ?></h1>
    </header>
    <div class="main__body">
