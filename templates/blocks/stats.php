<?php /** @var array $d */
$items = isset($d['items']) && is_array($d['items']) ? $d['items'] : [];
?>
<section class="stats">
  <div class="container">
    <div class="stats__card reveal">
      <?php foreach ($items as $item):
        $val = $item['value'] ?? '';
        preg_match('/(\d+)\s*([^\d]*)$/u', $val, $m);
        $count  = $m[1] ?? '0';
        $suffix = isset($m[2]) ? trim($m[2]) : '';
      ?>
        <div class="stat">
          <span class="stat__value" data-count="<?= esc($count) ?>" data-suffix="<?= esc($suffix) ?>"><?= esc($val) ?></span>
          <span class="stat__label" <?= bil_attrs($item, 'label') ?>><?= bil_text($item, 'label') ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
