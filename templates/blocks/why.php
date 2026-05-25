<?php /** @var array $d */
$items = isset($d['items']) && is_array($d['items']) ? $d['items'] : [];
?>
<section class="why" id="why">
  <div class="container">
    <div class="section__head reveal">
      <span class="eyebrow eyebrow--dark" <?= bil_attrs($d, 'eyebrow') ?>><?= bil_text($d, 'eyebrow') ?></span>
      <h2 class="section__title section__title--center section__title--light">
        <span <?= bil_attrs($d, 'title') ?>><?= bil_text($d, 'title') ?></span>
        <span class="text-accent"><?= esc($d['title_accent'] ?? '') ?></span><span data-ar="؟" data-en="?">؟</span>
      </h2>
      <p class="section__text section__text--center section__text--light" <?= bil_attrs($d, 'subtitle') ?>><?= bil_text($d, 'subtitle') ?></p>
    </div>

    <div class="why__grid">
      <?php foreach ($items as $item): ?>
        <article class="why-card reveal">
          <span class="why-card__icon"><?= content_icon($item['icon'] ?? 'globe', '#fff', 26) ?></span>
          <h3 class="why-card__title" <?= bil_attrs($item, 'title') ?>><?= bil_text($item, 'title') ?></h3>
          <p class="why-card__text" <?= bil_attrs($item, 'text') ?>><?= bil_text($item, 'text') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
