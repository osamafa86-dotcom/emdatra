<?php
/**
 * Database-driven public homepage.
 * Global settings + the site shell come from includes/site_*.php;
 * the page body is the ordered list of visible blocks.
 */
require_once __DIR__ . '/includes/site_boot.php';

$pageActive = 'home';
$blocks = get_blocks('home', true);
$types  = block_types();

include __DIR__ . '/includes/site_header.php';
?>
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
<?php include __DIR__ . '/includes/site_footer.php'; ?>
