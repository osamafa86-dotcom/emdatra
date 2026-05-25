<?php
/**
 * Public product / commodity catalog page.
 */
require_once __DIR__ . '/includes/site_boot.php';
require_once __DIR__ . '/includes/catalog.php';

$pageActive  = 'catalog';
$pageTitleAr = 'كتالوج السلع | ' . ($brand['name'] ?? 'emdatra');
$pageTitleEn = 'Product Catalog | ' . ($brand['name'] ?? 'emdatra');
$pageDescAr  = 'تصفّح السلع والمنتجات التي نستوردها ونصدّرها — مع بلد المنشأ والحد الأدنى للطلب.';

$products = [];
$cats = [];
try { $products = products_all(true); $cats = product_categories(); } catch (Throwable $e) {}

include __DIR__ . '/includes/site_header.php';
?>
  <main class="catalog">
    <div class="container">
      <div class="section__head">
        <span class="eyebrow" data-ar="كتالوج السلع" data-en="Product Catalog">كتالوج السلع</span>
        <h2 class="section__title section__title--center"><span data-ar="ماذا" data-en="What we">ماذا</span> <span class="text-accent" data-ar="نتاجر به" data-en="trade">نتاجر به</span></h2>
        <p class="section__text section__text--center" data-ar="مجموعة من السلع والمنتجات التي نوفّرها استيرادًا وتصديرًا. اطلب عرض سعر لأي منتج." data-en="A selection of the goods we supply for import and export. Request a quote for any product.">مجموعة من السلع والمنتجات التي نوفّرها استيرادًا وتصديرًا. اطلب عرض سعر لأي منتج.</p>
      </div>

      <?php if ($cats): ?>
        <div class="cat-filter" id="catFilter">
          <button type="button" class="cat-chip is-active" data-cat="" data-ar="الكل" data-en="All">الكل</button>
          <?php foreach ($cats as $c): ?>
            <button type="button" class="cat-chip" data-cat="<?= esc($c['category_ar']) ?>" data-ar="<?= esc($c['category_ar']) ?>" data-en="<?= esc($c['category_en'] ?: $c['category_ar']) ?>"><?= esc($c['category_ar']) ?></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <?php if (!$products): ?>
        <p class="catalog-empty" data-ar="سيُضاف الكتالوج قريبًا — تواصل معنا لمعرفة ما نوفّره." data-en="The catalog will be added soon — contact us to learn what we offer.">سيُضاف الكتالوج قريبًا — تواصل معنا لمعرفة ما نوفّره.</p>
      <?php else: ?>
        <div class="prod-grid" id="prodGrid">
          <?php foreach ($products as $p): ?>
            <article class="prod-card" data-cat="<?= esc($p['category_ar']) ?>">
              <div class="prod-card__media">
                <?php if (!empty($p['image'])): ?>
                  <img src="<?= esc($p['image']) ?>" alt="<?= esc($p['name_ar']) ?>" loading="lazy">
                <?php else: ?>
                  <span class="prod-card__ph"><svg viewBox="0 0 24 24" width="46" height="46" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><path d="m21 16-9 5-9-5V8l9-5 9 5Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg></span>
                <?php endif; ?>
              </div>
              <div class="prod-card__body">
                <?php if (!empty($p['category_ar'])): ?><span class="prod-card__cat" data-ar="<?= esc($p['category_ar']) ?>" data-en="<?= esc($p['category_en'] ?: $p['category_ar']) ?>"><?= esc($p['category_ar']) ?></span><?php endif; ?>
                <h3 class="prod-card__name" data-ar="<?= esc($p['name_ar']) ?>" data-en="<?= esc($p['name_en'] ?: $p['name_ar']) ?>"><?= esc($p['name_ar']) ?></h3>
                <?php if (!empty($p['desc_ar'])): ?><p class="prod-card__desc" data-ar="<?= esc($p['desc_ar']) ?>" data-en="<?= esc($p['desc_en'] ?: $p['desc_ar']) ?>"><?= esc($p['desc_ar']) ?></p><?php endif; ?>
                <div class="prod-card__meta">
                  <?php if (!empty($p['origin'])): ?><span><b data-ar="المنشأ" data-en="Origin">المنشأ</b>: <?= esc($p['origin']) ?></span><?php endif; ?>
                  <?php if (!empty($p['moq'])): ?><span><b data-ar="أقل كمية" data-en="MOQ">أقل كمية</b>: <?= esc($p['moq']) ?> <span data-ar="<?= esc($p['unit_ar'] ?? '') ?>" data-en="<?= esc($p['unit_en'] ?: ($p['unit_ar'] ?? '')) ?>"><?= esc($p['unit_ar'] ?? '') ?></span></span><?php endif; ?>
                </div>
                <button type="button" class="btn btn--ghost btn--sm prod-card__cta" data-quote-product="<?= esc($p['name_ar']) ?>" data-ar="اطلب عرض سعر" data-en="Request a quote">اطلب عرض سعر</button>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <script>
  (function () {
    var filter = document.getElementById('catFilter'), grid = document.getElementById('prodGrid');
    if (filter && grid) {
      filter.addEventListener('click', function (e) {
        var chip = e.target.closest('.cat-chip'); if (!chip) return;
        filter.querySelectorAll('.cat-chip').forEach(function (c) { c.classList.remove('is-active'); });
        chip.classList.add('is-active');
        var cat = chip.getAttribute('data-cat');
        grid.querySelectorAll('.prod-card').forEach(function (card) {
          card.style.display = (!cat || card.getAttribute('data-cat') === cat) ? '' : 'none';
        });
      });
    }
    document.addEventListener('click', function (e) {
      var b = e.target.closest('[data-quote-product]'); if (!b) return;
      e.preventDefault();
      var p = document.querySelector('#quoteForm [name=product]'); if (p) p.value = b.getAttribute('data-quote-product');
      if (window.emdOpenQuote) window.emdOpenQuote();
    });
  })();
  </script>
<?php include __DIR__ . '/includes/site_footer.php'; ?>
