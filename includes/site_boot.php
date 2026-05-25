<?php
/**
 * Shared bootstrap for all public pages.
 * Loads global settings and exposes helpers used by the site shell.
 */
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/icons.php';

$brand       = setting('brand', ['name' => 'emdatra', 'tag_ar' => 'للاستيراد والتصدير', 'tag_en' => 'Import & Export', 'logo' => 'assets/logo.png']);
$nav         = setting('nav', []);
$headerCta   = setting('header_cta', ['ar' => 'اطلب عرض سعر', 'en' => 'Get a Quote', 'href' => '#contact']);
$contactInfo = setting('contact_info', []);
$socials     = setting('socials', []);
$footerS     = setting('footer', []);
$seo         = setting('seo', []);
$logo        = $brand['logo'] ?? 'assets/logo.png';

$servicesItems = [];
foreach (get_blocks('home', true) as $b) {
    if ($b['type'] === 'services') {
        $sd = block_data($b);
        $servicesItems = $sd['items'] ?? [];
        break;
    }
}

/** Hash links point to homepage sections; prefix them when off the homepage. */
function nav_href($href, $isHome)
{
    $href = (string) $href;
    if ($href !== '' && $href[0] === '#') return $isHome ? $href : 'index.php' . $href;
    return $href;
}
