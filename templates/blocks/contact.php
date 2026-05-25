<?php /** @var array $d */
$ci = setting('contact_info', []);
?>
<section class="contact section section--alt" id="contact">
  <div class="container">
    <div class="section__head reveal">
      <span class="eyebrow" <?= bil_attrs($d, 'eyebrow') ?>><?= bil_text($d, 'eyebrow') ?></span>
      <h2 class="section__title section__title--center">
        <span <?= bil_attrs($d, 'title') ?>><?= bil_text($d, 'title') ?></span>
        <span class="text-accent" <?= bil_attrs($d, 'title_accent') ?>><?= bil_text($d, 'title_accent') ?></span>
      </h2>
      <p class="section__text section__text--center" <?= bil_attrs($d, 'subtitle') ?>><?= bil_text($d, 'subtitle') ?></p>
    </div>

    <div class="contact__inner">
      <form class="contact__form reveal" id="contactForm" novalidate>
        <div class="form-row">
          <div class="field">
            <label for="name" data-ar="الاسم الكامل" data-en="Full name">الاسم الكامل</label>
            <input type="text" id="name" name="name" required data-ar-ph="أدخل اسمك" data-en-ph="Enter your name" placeholder="أدخل اسمك" />
          </div>
          <div class="field">
            <label for="email" data-ar="البريد الإلكتروني" data-en="Email">البريد الإلكتروني</label>
            <input type="email" id="email" name="email" required data-ar-ph="example@email.com" data-en-ph="example@email.com" placeholder="example@email.com" />
          </div>
        </div>
        <div class="field">
          <label for="phone" data-ar="رقم الهاتف" data-en="Phone">رقم الهاتف</label>
          <input type="tel" id="phone" name="phone" data-ar-ph="‎+966 5X XXX XXXX" data-en-ph="‎+966 5X XXX XXXX" placeholder="‎+966 5X XXX XXXX" />
        </div>
        <div class="field">
          <label for="message" data-ar="رسالتك" data-en="Your message">رسالتك</label>
          <textarea id="message" name="message" rows="4" required data-ar-ph="اكتب رسالتك هنا..." data-en-ph="Write your message here..." placeholder="اكتب رسالتك هنا..."></textarea>
        </div>
        <div class="hp" aria-hidden="true">
          <label>Website</label>
          <input type="text" name="website" tabindex="-1" autocomplete="off" />
        </div>
        <button type="submit" class="btn btn--primary btn--block" data-ar="إرسال الرسالة" data-en="Send Message">إرسال الرسالة</button>
        <p class="form-status" id="formStatus" role="status" aria-live="polite"></p>
      </form>

      <aside class="contact__info reveal">
        <h3 class="contact__info-title" <?= bil_attrs($d, 'info_title') ?>><?= bil_text($d, 'info_title') ?></h3>
        <p class="contact__info-sub" <?= bil_attrs($d, 'info_sub') ?>><?= bil_text($d, 'info_sub') ?></p>
        <ul class="info-list">
          <li>
            <span class="info-list__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <span class="info-list__col"><span class="info-list__label" data-ar="العنوان" data-en="Address">العنوان</span><span class="info-list__value" data-ar="<?= esc($ci['address_ar'] ?? '') ?>" data-en="<?= esc($ci['address_en'] ?? '') ?>"><?= esc($ci['address_ar'] ?? '') ?></span></span>
          </li>
          <li>
            <span class="info-list__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2 4.2 2 2 0 0 1 4 2h3a2 2 0 0 1 2 1.7c.1.9.3 1.8.6 2.6a2 2 0 0 1-.5 2.1L8 9.6a16 16 0 0 0 6 6l1.2-1.1a2 2 0 0 1 2.1-.5c.8.3 1.7.5 2.6.6A2 2 0 0 1 22 16.9Z"/></svg></span>
            <span class="info-list__col"><span class="info-list__label" data-ar="الهاتف" data-en="Phone">الهاتف</span><span class="info-list__value" dir="ltr"><?= esc($ci['phone'] ?? '') ?></span></span>
          </li>
          <li>
            <span class="info-list__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg></span>
            <span class="info-list__col"><span class="info-list__label" data-ar="البريد الإلكتروني" data-en="Email">البريد الإلكتروني</span><span class="info-list__value" dir="ltr"><?= esc($ci['email'] ?? '') ?></span></span>
          </li>
          <li>
            <span class="info-list__icon"><svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
            <span class="info-list__col"><span class="info-list__label" data-ar="ساعات العمل" data-en="Working hours">ساعات العمل</span><span class="info-list__value" data-ar="<?= esc($ci['hours_ar'] ?? '') ?>" data-en="<?= esc($ci['hours_en'] ?? '') ?>"><?= esc($ci['hours_ar'] ?? '') ?></span></span>
          </li>
        </ul>
      </aside>
    </div>
  </div>
</section>
