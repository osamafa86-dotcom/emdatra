<?php
/**
 * Editor schema for each block type and the icon set.
 * Field kinds: text | textarea | bil | bil_area | icon
 *  - bil / bil_area carry an explicit 'ar' and 'en' content key.
 *  - text / textarea / icon carry a single 'key'.
 */

require_once __DIR__ . '/helpers.php';

function icon_options()
{
    return [
        'import'  => 'استيراد (سهم لأسفل)',
        'export'  => 'تصدير (سهم لأعلى)',
        'truck'   => 'شحن (شاحنة)',
        'shield'  => 'درع / حماية',
        'refresh' => 'دورة / سلسلة',
        'chart'   => 'رسم بياني',
        'globe'   => 'كرة أرضية',
        'tag'     => 'سعر / وسم',
        'support' => 'دعم / سماعة',
    ];
}

function block_schema($type)
{
    $f = function ($kind, $a, $b = null, $label = '') {
        if ($kind === 'bil' || $kind === 'bil_area') {
            return ['kind' => $kind, 'ar' => $a, 'en' => $b, 'label' => $label];
        }
        return ['kind' => $kind, 'key' => $a, 'label' => $b];
    };

    switch ($type) {
        case 'hero':
            return [
                'fields' => [
                    $f('bil', 'eyebrow_ar', 'eyebrow_en', 'السطر التمهيدي'),
                    $f('bil', 'title_ar', 'title_en', 'العنوان — الجزء الأول'),
                    $f('bil', 'title_accent_ar', 'title_accent_en', 'العنوان — الجزء المميّز (برتقالي)'),
                    $f('bil_area', 'sub_ar', 'sub_en', 'النص الوصفي'),
                    $f('bil', 'btn1_ar', 'btn1_en', 'الزر الأول — النص'),
                    $f('text', 'btn1_href', 'الزر الأول — الرابط'),
                    $f('bil', 'btn2_ar', 'btn2_en', 'الزر الثاني — النص'),
                    $f('text', 'btn2_href', 'الزر الثاني — الرابط'),
                    $f('text', 'card1_value', 'البطاقة 1 — الرقم'),
                    $f('bil', 'card1_label_ar', 'card1_label_en', 'البطاقة 1 — الوصف'),
                    $f('text', 'card2_value', 'البطاقة 2 — الرقم'),
                    $f('bil', 'card2_label_ar', 'card2_label_en', 'البطاقة 2 — الوصف'),
                ],
                'repeaters' => [],
            ];

        case 'stats':
            return [
                'fields' => [],
                'repeaters' => [[
                    'key' => 'items', 'label' => 'الأرقام', 'item_label' => 'رقم',
                    'subfields' => [
                        $f('text', 'value', 'الرقم (مثل +15)'),
                        $f('bil', 'label_ar', 'label_en', 'الوصف'),
                    ],
                ]],
            ];

        case 'about':
            return [
                'fields' => [
                    $f('bil', 'eyebrow_ar', 'eyebrow_en', 'السطر التمهيدي'),
                    $f('bil', 'title_accent_ar', 'title_accent_en', 'العنوان — الجزء المميّز'),
                    $f('bil', 'title_ar', 'title_en', 'العنوان — التكملة'),
                    $f('bil_area', 'text_ar', 'text_en', 'النص'),
                    $f('text', 'badge_value', 'الشارة — الرقم'),
                    $f('bil', 'badge_label_ar', 'badge_label_en', 'الشارة — الوصف'),
                    $f('bil', 'panel_label_ar', 'panel_label_en', 'نص اللوحة الجانبية'),
                ],
                'repeaters' => [[
                    'key' => 'checklist', 'label' => 'قائمة المزايا', 'item_label' => 'ميزة',
                    'subfields' => [
                        $f('bil', 'ar', 'en', 'النص'),
                    ],
                ]],
            ];

        case 'services':
            return [
                'fields' => [
                    $f('bil', 'eyebrow_ar', 'eyebrow_en', 'السطر التمهيدي'),
                    $f('bil', 'title_ar', 'title_en', 'العنوان — الجزء الأول'),
                    $f('bil', 'title_accent_ar', 'title_accent_en', 'العنوان — الجزء المميّز'),
                    $f('bil', 'title_after_ar', 'title_after_en', 'العنوان — الجزء الأخير'),
                    $f('bil_area', 'subtitle_ar', 'subtitle_en', 'النص الوصفي'),
                ],
                'repeaters' => [[
                    'key' => 'items', 'label' => 'الخدمات', 'item_label' => 'خدمة',
                    'subfields' => [
                        $f('icon', 'icon', 'الأيقونة'),
                        $f('bil', 'title_ar', 'title_en', 'العنوان'),
                        $f('bil_area', 'text_ar', 'text_en', 'الوصف'),
                    ],
                ]],
            ];

        case 'why':
            return [
                'fields' => [
                    $f('bil', 'eyebrow_ar', 'eyebrow_en', 'السطر التمهيدي'),
                    $f('bil', 'title_ar', 'title_en', 'العنوان'),
                    $f('text', 'title_accent', 'الكلمة المميّزة (مثل emdatra)'),
                    $f('bil_area', 'subtitle_ar', 'subtitle_en', 'النص الوصفي'),
                ],
                'repeaters' => [[
                    'key' => 'items', 'label' => 'المزايا', 'item_label' => 'ميزة',
                    'subfields' => [
                        $f('icon', 'icon', 'الأيقونة'),
                        $f('bil', 'title_ar', 'title_en', 'العنوان'),
                        $f('bil_area', 'text_ar', 'text_en', 'الوصف'),
                    ],
                ]],
            ];

        case 'contact':
            return [
                'fields' => [
                    $f('bil', 'eyebrow_ar', 'eyebrow_en', 'السطر التمهيدي'),
                    $f('bil', 'title_ar', 'title_en', 'العنوان — الجزء الأول'),
                    $f('bil', 'title_accent_ar', 'title_accent_en', 'العنوان — الجزء المميّز'),
                    $f('bil_area', 'subtitle_ar', 'subtitle_en', 'النص الوصفي'),
                    $f('bil', 'info_title_ar', 'info_title_en', 'عنوان معلومات التواصل'),
                    $f('bil_area', 'info_sub_ar', 'info_sub_en', 'وصف معلومات التواصل'),
                ],
                'repeaters' => [],
            ];

        case 'cta':
            return [
                'fields' => [
                    $f('bil', 'title_ar', 'title_en', 'العنوان'),
                    $f('bil_area', 'text_ar', 'text_en', 'النص'),
                    $f('bil', 'btn_ar', 'btn_en', 'الزر — النص'),
                    $f('text', 'btn_href', 'الزر — الرابط'),
                ],
                'repeaters' => [],
            ];
    }

    return ['fields' => [], 'repeaters' => []];
}
