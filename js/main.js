/* =========================================================
   emdatra — interactions
   i18n (AR/EN), mobile menu, scrollspy, reveal, counters, form
   ========================================================= */
(function () {
  'use strict';
  document.documentElement.classList.add('js');

  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

  /* ---------- i18n ---------- */
  var LANG_KEY = 'emdatra_lang';
  var TITLES = { ar: 'إمداترا | حلول الاستيراد والتصدير', en: 'emdatra | Import & Export Solutions' };

  function applyLang(lang) {
    var html = document.documentElement;
    html.lang = lang;
    html.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
    $$('[data-ar]').forEach(function (el) {
      var v = el.getAttribute('data-' + lang);
      if (v !== null) el.textContent = v;
    });
    $$('[data-ar-ph]').forEach(function (el) {
      var v = el.getAttribute('data-' + lang + '-ph');
      if (v !== null) el.setAttribute('placeholder', v);
    });
    document.title = TITLES[lang] || TITLES.ar;
    var lbl = $('#langLabel');
    if (lbl) lbl.textContent = lang === 'ar' ? 'EN' : 'ع';
    try { localStorage.setItem(LANG_KEY, lang); } catch (e) {}
  }

  var curLang = 'ar';
  try { curLang = localStorage.getItem(LANG_KEY) || 'ar'; } catch (e) {}
  applyLang(curLang);

  var langBtn = $('#langToggle');
  if (langBtn) {
    langBtn.addEventListener('click', function () {
      curLang = curLang === 'ar' ? 'en' : 'ar';
      applyLang(curLang);
    });
  }

  /* ---------- mobile menu ---------- */
  var nav = $('#nav'), menuBtn = $('#menuBtn'), backdrop = $('#navBackdrop');
  function setMenu(open) {
    if (!nav) return;
    nav.classList.toggle('open', open);
    if (menuBtn) { menuBtn.classList.toggle('open', open); menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false'); }
    if (backdrop) backdrop.classList.toggle('show', open);
    document.body.style.overflow = open ? 'hidden' : '';
  }
  if (menuBtn) menuBtn.addEventListener('click', function () { setMenu(!nav.classList.contains('open')); });
  if (backdrop) backdrop.addEventListener('click', function () { setMenu(false); });

  /* ---------- smooth anchor scroll with header offset ---------- */
  $$('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href');
      if (id === '#') return;
      var t = document.querySelector(id);
      if (!t) return;
      e.preventDefault();
      setMenu(false);
      var top = t.getBoundingClientRect().top + window.scrollY - 70;
      window.scrollTo({ top: top, behavior: 'smooth' });
    });
  });

  /* ---------- scrollspy ---------- */
  var sections = $$('section[id]');
  var navLinks = $$('.nav__link');
  function spy() {
    var pos = window.scrollY + window.innerHeight * 0.32;
    var cur = '';
    sections.forEach(function (s) { if (pos >= s.offsetTop) cur = s.id; });
    navLinks.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + cur); });
  }

  /* ---------- header state + to-top ---------- */
  var header = $('#header'), toTop = $('#toTop');
  function onScroll() {
    var y = window.scrollY;
    if (header) header.classList.toggle('scrolled', y > 30);
    if (toTop) toTop.classList.toggle('show', y > 500);
    spy();
  }
  window.addEventListener('scroll', onScroll, { passive: true });

  /* ---------- reveal on scroll + counters ---------- */
  var counted = false;
  function runCounters() {
    if (counted) return; counted = true;
    $$('.stat__value').forEach(function (el) {
      var target = parseInt(el.getAttribute('data-count') || '0', 10);
      var suffix = el.getAttribute('data-suffix') || '';
      var dur = 1400, startT = performance.now();
      function tick(now) {
        var p = Math.min((now - startT) / dur, 1);
        var val = Math.floor((1 - Math.pow(1 - p, 3)) * target);
        el.textContent = '+' + val + suffix;
        if (p < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    });
  }

  var reveals = $$('.reveal');
  if ('IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          en.target.classList.add('in');
          if (en.target.classList.contains('stats__card')) runCounters();
          io.unobserve(en.target);
        }
      });
    }, { threshold: 0.15 });
    reveals.forEach(function (el) { io.observe(el); });
  } else {
    reveals.forEach(function (el) { el.classList.add('in'); });
    runCounters();
  }

  /* ---------- contact form ---------- */
  var form = $('#contactForm'), status = $('#formStatus');
  var MSG = {
    ar: { ok: 'تم إرسال رسالتك بنجاح! سنتواصل معك قريبًا.', err: 'الرجاء تعبئة الحقول المطلوبة بشكل صحيح.' },
    en: { ok: 'Your message has been sent! We will contact you soon.', err: 'Please fill in the required fields correctly.' }
  };
  function isEmail(v) { return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v); }
  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var valid = true;
      ['name', 'email', 'message'].forEach(function (id) {
        var f = $('#' + id);
        var ok = f.value.trim() !== '' && (id !== 'email' || isEmail(f.value.trim()));
        f.classList.toggle('invalid', !ok);
        if (!ok) valid = false;
      });
      var L = document.documentElement.lang === 'en' ? 'en' : 'ar';
      if (!valid) { status.textContent = MSG[L].err; status.className = 'form-status err'; return; }
      status.textContent = MSG[L].ok;
      status.className = 'form-status ok';
      form.reset();
    });
    $$('#contactForm input, #contactForm textarea').forEach(function (f) {
      f.addEventListener('input', function () { f.classList.remove('invalid'); });
    });
  }

  onScroll();
})();
