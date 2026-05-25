<?php /** @var array $d */ ?>
<section class="cta-band">
  <div class="container cta-band__inner reveal">
    <div class="cta-band__text">
      <h2 <?= bil_attrs($d, 'title') ?>><?= bil_text($d, 'title') ?></h2>
      <p <?= bil_attrs($d, 'text') ?>><?= bil_text($d, 'text') ?></p>
    </div>
    <a href="<?= esc($d['btn_href'] ?? '#contact') ?>" class="btn btn--white" <?= bil_attrs($d, 'btn') ?>><?= bil_text($d, 'btn') ?></a>
  </div>
</section>
