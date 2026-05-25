<?php /** @var array $d */
$checklist = isset($d['checklist']) && is_array($d['checklist']) ? $d['checklist'] : [];
?>
<section class="about section" id="about">
  <div class="container about__inner">
    <div class="about__text reveal">
      <span class="eyebrow" <?= bil_attrs($d, 'eyebrow') ?>><?= bil_text($d, 'eyebrow') ?></span>
      <h2 class="section__title">
        <span class="text-accent" <?= bil_attrs($d, 'title_accent') ?>><?= bil_text($d, 'title_accent') ?></span>
        <span <?= bil_attrs($d, 'title') ?>><?= bil_text($d, 'title') ?></span>
      </h2>
      <p class="section__text" <?= bil_attrs($d, 'text') ?>><?= bil_text($d, 'text') ?></p>
      <ul class="checklist">
        <?php foreach ($checklist as $c): ?>
          <li>
            <span class="check" aria-hidden="true"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="#FF6B1A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
            <span data-ar="<?= esc($c['ar'] ?? '') ?>" data-en="<?= esc($c['en'] ?? '') ?>"><?= esc($c['ar'] ?? '') ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="about__visual reveal" aria-hidden="true">
      <div class="about__panel">
        <svg class="about__globe" viewBox="0 0 24 24" fill="none" stroke="#FF6B1A" stroke-width="1" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.6 2.7 3.9 6 3.9 9s-1.3 6.3-3.9 9c-2.6-2.7-3.9-6-3.9-9S9.4 5.7 12 3Z"/></svg>
        <svg class="about__routes" viewBox="0 0 360 220" fill="none" preserveAspectRatio="none">
          <path d="M40 170 Q150 30 320 80" stroke="#FF6B1A" stroke-width="2.5" stroke-dasharray="1 9" stroke-linecap="round"/>
          <path d="M55 60 Q190 190 315 150" stroke="#fff" stroke-opacity="0.45" stroke-width="2.5" stroke-dasharray="1 9" stroke-linecap="round"/>
          <circle cx="40" cy="170" r="7" fill="#FF6B1A"/><circle cx="320" cy="80" r="7" fill="#FF6B1A"/>
          <circle cx="55" cy="60" r="6" fill="#fff" fill-opacity="0.8"/><circle cx="315" cy="150" r="6" fill="#fff" fill-opacity="0.8"/>
        </svg>
        <span class="about__panel-label" <?= bil_attrs($d, 'panel_label') ?>><?= bil_text($d, 'panel_label') ?></span>
      </div>
      <div class="about__badge">
        <div class="about__badge-col">
          <span class="about__badge-value"><?= esc($d['badge_value'] ?? '') ?></span>
          <span class="about__badge-label" <?= bil_attrs($d, 'badge_label') ?>><?= bil_text($d, 'badge_label') ?></span>
        </div>
        <span class="about__badge-icon">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="#FF6B1A" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M8.5 13.5 7 22l5-3 5 3-1.5-8.5"/></svg>
        </span>
      </div>
    </div>
  </div>
</section>
