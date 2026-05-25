<?php /** @var array $d */
$items = isset($d['items']) && is_array($d['items']) ? $d['items'] : [];
?>
<section class="services section section--alt" id="services">
  <div class="container">
    <div class="section__head reveal">
      <span class="eyebrow" <?= bil_attrs($d, 'eyebrow') ?>><?= bil_text($d, 'eyebrow') ?></span>
      <h2 class="section__title section__title--center">
        <span <?= bil_attrs($d, 'title') ?>><?= bil_text($d, 'title') ?></span>
        <span class="text-accent" <?= bil_attrs($d, 'title_accent') ?>><?= bil_text($d, 'title_accent') ?></span>
        <span <?= bil_attrs($d, 'title_after') ?>><?= bil_text($d, 'title_after') ?></span>
      </h2>
      <p class="section__text section__text--center" <?= bil_attrs($d, 'subtitle') ?>><?= bil_text($d, 'subtitle') ?></p>
    </div>

    <div class="services__grid">
      <?php foreach ($items as $item): ?>
        <article class="service-card reveal">
          <span class="service-card__icon"><?= content_icon($item['icon'] ?? 'globe', '#FF6B1A', 27) ?></span>
          <h3 class="service-card__title" <?= bil_attrs($item, 'title') ?>><?= bil_text($item, 'title') ?></h3>
          <p class="service-card__text" <?= bil_attrs($item, 'text') ?>><?= bil_text($item, 'text') ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
