<?php /** @var array $d */ ?>
<section class="hero" id="home">
  <div class="hero__glow" aria-hidden="true"></div>
  <div class="container hero__inner">
    <div class="hero__text reveal">
      <span class="eyebrow eyebrow--dark">
        <span <?= bil_attrs($d, 'eyebrow') ?>><?= bil_text($d, 'eyebrow') ?></span>
        <i class="eyebrow__dot" aria-hidden="true"></i>
      </span>
      <h1 class="hero__title">
        <span <?= bil_attrs($d, 'title') ?>><?= bil_text($d, 'title') ?></span>
        <span class="text-accent" <?= bil_attrs($d, 'title_accent') ?>><?= bil_text($d, 'title_accent') ?></span>
      </h1>
      <p class="hero__sub" <?= bil_attrs($d, 'sub') ?>><?= bil_text($d, 'sub') ?></p>
      <div class="hero__cta">
        <a href="<?= esc($d['btn1_href'] ?? '#contact') ?>" class="btn btn--primary" <?= bil_attrs($d, 'btn1') ?>><?= bil_text($d, 'btn1') ?></a>
        <a href="<?= esc($d['btn2_href'] ?? '#services') ?>" class="btn btn--ghost" <?= bil_attrs($d, 'btn2') ?>><?= bil_text($d, 'btn2') ?></a>
      </div>
    </div>

    <div class="hero__visual reveal" aria-hidden="true">
      <div class="hero__panel">
        <span class="hero__circle"></span>
        <span class="hero__ring"></span>
        <svg class="hero__globe" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.6 2.7 3.9 6 3.9 9s-1.3 6.3-3.9 9c-2.6-2.7-3.9-6-3.9-9S9.4 5.7 12 3Z"/></svg>
      </div>
      <div class="float-card float-card--a">
        <div class="float-card__col">
          <span class="float-card__label" <?= bil_attrs($d, 'card1_label') ?>><?= bil_text($d, 'card1_label') ?></span>
          <span class="float-card__value"><?= esc($d['card1_value'] ?? '') ?></span>
        </div>
        <span class="float-card__icon">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#FF6B1A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
        </span>
      </div>
      <div class="float-card float-card--b">
        <div class="float-card__col">
          <span class="float-card__label" <?= bil_attrs($d, 'card2_label') ?>><?= bil_text($d, 'card2_label') ?></span>
          <span class="float-card__value"><?= esc($d['card2_value'] ?? '') ?></span>
        </div>
        <span class="float-card__icon">
          <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#FF6B1A" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.6 2.7 3.9 6 3.9 9s-1.3 6.3-3.9 9c-2.6-2.7-3.9-6-3.9-9S9.4 5.7 12 3Z"/></svg>
        </span>
      </div>
    </div>
  </div>
</section>
