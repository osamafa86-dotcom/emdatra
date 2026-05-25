<?php
/**
 * One-time content seeder: imports the current emdatra website content
 * (settings + blocks) into the database. Runs only when no blocks exist.
 */

require_once __DIR__ . '/helpers.php';

function content_is_seeded()
{
    return (int)db()->query('SELECT COUNT(*) FROM emd_blocks')->fetchColumn() > 0;
}

function seed_default_content()
{
    if (content_is_seeded()) {
        return false;
    }

    /* ---------- global settings ---------- */
    save_setting('brand', [
        'name'   => 'emdatra',
        'tag_ar' => 'للاستيراد والتصدير',
        'tag_en' => 'Import & Export',
        'logo'   => 'assets/logo.png',
    ]);

    save_setting('seo', [
        'title_ar' => 'إمداترا | حلول الاستيراد والتصدير',
        'title_en' => 'emdatra | Import & Export Solutions',
        'desc_ar'  => 'إمداترا شركة متخصصة في الاستيراد والتصدير، نقدّم حلولاً لوجستية متكاملة وسلاسل إمداد موثوقة تربط أعمالك بالأسواق العالمية.',
        'desc_en'  => 'emdatra specializes in import and export, delivering integrated logistics and reliable supply chains that connect your business to global markets.',
    ]);

    save_setting('nav', [
        ['ar' => 'الرئيسية', 'en' => 'Home',     'href' => '#home'],
        ['ar' => 'من نحن',   'en' => 'About',    'href' => '#about'],
        ['ar' => 'الخدمات',  'en' => 'Services', 'href' => '#services'],
        ['ar' => 'لماذا نحن', 'en' => 'Why Us',   'href' => '#why'],
        ['ar' => 'تواصل',    'en' => 'Contact',  'href' => '#contact'],
    ]);

    save_setting('header_cta', ['ar' => 'اطلب عرض سعر', 'en' => 'Get a Quote', 'href' => '#contact']);

    save_setting('contact_info', [
        'address_ar' => 'الرياض، المملكة العربية السعودية',
        'address_en' => 'Riyadh, Saudi Arabia',
        'phone'      => '+966 50 123 4567',
        'email'      => 'info@emdatra.com',
        'hours_ar'   => 'الأحد – الخميس، 9 ص – 6 م',
        'hours_en'   => 'Sun – Thu, 9 AM – 6 PM',
    ]);

    save_setting('socials', [
        'instagram' => '#',
        'x'         => '#',
        'linkedin'  => '#',
        'facebook'  => '#',
    ]);

    save_setting('footer', [
        'desc_ar' => 'إمداترا شريكك الموثوق في حلول الاستيراد والتصدير، نربط أعمالك بالأسواق العالمية بكفاءة وأمان.',
        'desc_en' => 'emdatra is your trusted partner in import and export solutions, connecting your business to global markets efficiently and safely.',
        'copy_ar' => '© 2025 إمداترا. جميع الحقوق محفوظة.',
        'copy_en' => '© 2025 emdatra. All rights reserved.',
    ]);

    /* ---------- page blocks ---------- */
    $blocks = [];

    $blocks[] = ['type' => 'hero', 'content' => [
        'eyebrow_ar' => 'شريكك في التجارة الدولية', 'eyebrow_en' => 'Your International Trade Partner',
        'title_ar' => 'شريكك الأول في', 'title_en' => 'Your Premier Partner in',
        'title_accent_ar' => 'الاستيراد والتصدير', 'title_accent_en' => 'Import & Export',
        'sub_ar' => 'في إمداترا نقدّم حلولاً لوجستية متكاملة وسلاسل إمداد موثوقة تنقل بضائعك بأمان وكفاءة عبر الحدود — لنكون جسرك نحو الأسواق العالمية.',
        'sub_en' => 'At emdatra we deliver integrated logistics and reliable supply chains that move your goods safely and efficiently across borders — your bridge to global markets.',
        'btn1_ar' => 'احصل على استشارة', 'btn1_en' => 'Get a Consultation', 'btn1_href' => '#contact',
        'btn2_ar' => 'تعرّف على خدماتنا', 'btn2_en' => 'Explore Services', 'btn2_href' => '#services',
        'card1_value' => '+10,000', 'card1_label_ar' => 'شحنات تم تسليمها', 'card1_label_en' => 'Shipments delivered',
        'card2_value' => '+40', 'card2_label_ar' => 'دولة حول العالم', 'card2_label_en' => 'Countries worldwide',
    ]];

    $blocks[] = ['type' => 'stats', 'content' => [
        'items' => [
            ['value' => '+15',  'label_ar' => 'سنة من الخبرة',  'label_en' => 'Years of experience'],
            ['value' => '+40',  'label_ar' => 'دولة حول العالم', 'label_en' => 'Countries worldwide'],
            ['value' => '+500', 'label_ar' => 'عميل يثق بنا',    'label_en' => 'Clients trust us'],
            ['value' => '+10K', 'label_ar' => 'شحنة ناجحة',      'label_en' => 'Successful shipments'],
        ],
    ]];

    $blocks[] = ['type' => 'about', 'content' => [
        'eyebrow_ar' => 'من نحن', 'eyebrow_en' => 'About Us',
        'title_accent_ar' => 'خبرة عالمية', 'title_accent_en' => 'Global expertise',
        'title_ar' => 'في خدمة تجارتك المحلية', 'title_en' => 'serving your local trade',
        'text_ar' => 'تأسست إمداترا لتكون شريكك التجاري الموثوق الذي يربط الأسواق المحلية بالعالمية. نقدّم حلول استيراد وتصدير متكاملة مدعومة بشبكة واسعة من الشركاء وفريق خبراء يضمن وصول بضائعك بأعلى معايير الجودة وفي الوقت المحدد.',
        'text_en' => 'emdatra was founded to be your trusted trade partner connecting local markets to the world. We offer integrated import and export solutions backed by a wide partner network and an expert team that ensures your goods arrive at the highest quality standards and on time.',
        'checklist' => [
            ['ar' => 'شبكة شركاء في أكثر من 40 دولة حول العالم', 'en' => 'A partner network across 40+ countries worldwide'],
            ['ar' => 'فريق خبراء في التخليص الجمركي واللوجستيات', 'en' => 'Experts in customs clearance and logistics'],
            ['ar' => 'أسعار تنافسية وشفافية كاملة في التعامل', 'en' => 'Competitive pricing and full transparency'],
        ],
        'badge_value' => '+15', 'badge_label_ar' => 'عاماً من الخبرة', 'badge_label_en' => 'Years of experience',
        'panel_label_ar' => 'شبكة عالمية تربط أكثر من 40 دولة', 'panel_label_en' => 'A global network linking 40+ countries',
    ]];

    $blocks[] = ['type' => 'services', 'content' => [
        'eyebrow_ar' => 'خدماتنا', 'eyebrow_en' => 'Our Services',
        'title_ar' => 'حلول تجارية', 'title_accent_ar' => 'متكاملة', 'title_after_ar' => 'لتنمية أعمالك',
        'title_en' => 'Integrated', 'title_accent_en' => 'Trade Solutions', 'title_after_en' => 'to Grow Your Business',
        'subtitle_ar' => 'نقدّم باقة شاملة من الخدمات التي تغطّي رحلة بضائعك من المصدر وحتى الوصول إلى وجهتها النهائية.',
        'subtitle_en' => "We offer a comprehensive suite of services covering your cargo's journey from source to final destination.",
        'items' => [
            ['icon' => 'import',  'title_ar' => 'الاستيراد', 'title_en' => 'Import',
             'text_ar' => 'نوفّر خدمات استيراد شاملة من مختلف دول العالم بأفضل الأسعار ومعايير الجودة العالية.',
             'text_en' => 'Comprehensive import services from around the world at the best prices and high quality standards.'],
            ['icon' => 'export',  'title_ar' => 'التصدير', 'title_en' => 'Export',
             'text_ar' => 'نُصدّر منتجاتك إلى الأسواق العالمية مع إدارة كاملة لعمليات الشحن والتوثيق.',
             'text_en' => 'We export your products to global markets with full management of shipping and documentation.'],
            ['icon' => 'truck',   'title_ar' => 'الشحن واللوجستيات', 'title_en' => 'Shipping & Logistics',
             'text_ar' => 'حلول شحن بري وبحري وجوي مع تتبّع كامل لشحناتك لحظة بلحظة.',
             'text_en' => 'Land, sea and air freight solutions with full real-time tracking of your shipments.'],
            ['icon' => 'shield',  'title_ar' => 'التخليص الجمركي', 'title_en' => 'Customs Clearance',
             'text_ar' => 'إنجاز كافة الإجراءات الجمركية بسرعة واحترافية ودون أي تعقيد.',
             'text_en' => 'Completing all customs procedures quickly, professionally and without complications.'],
            ['icon' => 'refresh', 'title_ar' => 'سلاسل الإمداد', 'title_en' => 'Supply Chain',
             'text_ar' => 'إدارة متكاملة لسلسلة التوريد من المصدر وحتى الوجهة النهائية.',
             'text_en' => 'End-to-end supply chain management from source to final destination.'],
            ['icon' => 'chart',   'title_ar' => 'الاستشارات التجارية', 'title_en' => 'Trade Consulting',
             'text_ar' => 'استشارات متخصصة لدخول أسواق جديدة وتنمية أعمالك التجارية بثقة.',
             'text_en' => 'Specialized consulting to enter new markets and grow your trade with confidence.'],
        ],
    ]];

    $blocks[] = ['type' => 'why', 'content' => [
        'eyebrow_ar' => 'لماذا نحن', 'eyebrow_en' => 'Why Us',
        'title_ar' => 'لماذا تختار', 'title_en' => 'Why choose', 'title_accent' => 'emdatra',
        'subtitle_ar' => 'نتميّز بخبرة عميقة وشبكة عالمية وفريق متخصص يجعلنا الشريك الأمثل لتجارتك.',
        'subtitle_en' => 'Deep expertise, a global network and a dedicated team make us the ideal partner for your trade.',
        'items' => [
            ['icon' => 'shield', 'title_ar' => 'جودة مضمونة', 'title_en' => 'Guaranteed Quality',
             'text_ar' => 'نلتزم بأعلى معايير الجودة والمطابقة في كل شحنة نتعامل معها.',
             'text_en' => 'We uphold the highest quality and compliance standards on every shipment.'],
            ['icon' => 'globe',  'title_ar' => 'شبكة عالمية', 'title_en' => 'Global Network',
             'text_ar' => 'شبكة واسعة من الموردين والشركاء في أكثر من 40 دولة حول العالم.',
             'text_en' => 'A vast network of suppliers and partners in 40+ countries worldwide.'],
            ['icon' => 'tag',    'title_ar' => 'أسعار تنافسية', 'title_en' => 'Competitive Pricing',
             'text_ar' => 'نقدّم أفضل الأسعار في السوق مع شفافية كاملة دون رسوم خفية.',
             'text_en' => 'The best market prices with full transparency and no hidden fees.'],
            ['icon' => 'support', 'title_ar' => 'دعم متواصل', 'title_en' => 'Ongoing Support',
             'text_ar' => 'فريق دعم متخصص جاهز لمساعدتك والإجابة على استفساراتك.',
             'text_en' => 'A dedicated support team ready to assist you and answer your questions.'],
        ],
    ]];

    $blocks[] = ['type' => 'contact', 'content' => [
        'eyebrow_ar' => 'تواصل معنا', 'eyebrow_en' => 'Contact Us',
        'title_ar' => 'لنبدأ', 'title_accent_ar' => 'العمل معًا',
        'title_en' => "Let's start", 'title_accent_en' => 'working together',
        'subtitle_ar' => 'هل لديك استفسار أو ترغب في الحصول على عرض سعر؟ راسلنا وسنرد عليك في أقرب وقت.',
        'subtitle_en' => "Have a question or want a quote? Send us a message and we'll get back to you soon.",
        'info_title_ar' => 'معلومات التواصل', 'info_title_en' => 'Contact Information',
        'info_sub_ar' => 'نحن هنا للإجابة على استفساراتك ومساعدتك في كل خطوة من رحلتك التجارية.',
        'info_sub_en' => "We're here to answer your questions and help at every step of your trade journey.",
    ]];

    $blocks[] = ['type' => 'cta', 'content' => [
        'title_ar' => 'جاهز لتوسيع تجارتك إلى العالم؟', 'title_en' => 'Ready to take your trade global?',
        'text_ar' => 'تواصل مع فريقنا اليوم واحصل على استشارة مجانية لخدمات الاستيراد والتصدير وسلاسل الإمداد.',
        'text_en' => 'Talk to our team today and get a free consultation on import, export and supply chain services.',
        'btn_ar' => 'تواصل معنا الآن', 'btn_en' => 'Contact us now', 'btn_href' => '#contact',
    ]];

    $stmt = db()->prepare(
        'INSERT INTO emd_blocks (page, type, sort_order, is_visible, content) VALUES (?, ?, ?, 1, ?)'
    );
    $order = 1;
    foreach ($blocks as $b) {
        $stmt->execute(['home', $b['type'], $order++, json_encode($b['content'], JSON_UNESCAPED_UNICODE)]);
    }

    return true;
}
