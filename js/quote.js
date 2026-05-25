/* =========================================================
   emdatra — quote request modal
   ========================================================= */
(function () {
  'use strict';
  var modal = document.getElementById('quoteModal');
  if (!modal) return;
  var form = document.getElementById('quoteForm');
  var status = document.getElementById('quoteStatus');
  var submitBtn = form ? form.querySelector('button[type="submit"]') : null;
  var lastFocus = null;

  var MSG = {
    ar: {
      ok: 'تم استلام طلبك! سنعدّ لك عرض السعر ونتواصل معك قريبًا.',
      err: 'الرجاء تعبئة الاسم والمنتج وطريقة تواصل واحدة على الأقل.',
      sending: 'جارٍ الإرسال...',
      neterr: 'تعذّر إرسال الطلب حاليًا. حاول لاحقًا أو راسلنا على info@emdatra.com'
    },
    en: {
      ok: 'Your request was received! We will prepare your quote and contact you soon.',
      err: 'Please fill in your name, the product, and at least one contact method.',
      sending: 'Sending...',
      neterr: 'Could not send your request right now. Please try later or email info@emdatra.com'
    }
  };
  function L() { return document.documentElement.lang === 'en' ? 'en' : 'ar'; }
  function isEmail(v) { return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v); }

  function open() {
    lastFocus = document.activeElement;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    var first = form ? form.querySelector('input, textarea') : null;
    if (first) setTimeout(function () { first.focus(); }, 60);
  }
  function close() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-quote]');
    if (trigger) { e.preventDefault(); open(); return; }
    if (e.target.closest('[data-quote-close]')) { e.preventDefault(); close(); }
  });
  window.emdOpenQuote = open;
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && modal.classList.contains('open')) close();
  });

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var name = form.name.value.trim();
      var email = form.email.value.trim();
      var phone = form.phone.value.trim();
      var product = form.product.value.trim();
      var valid = name !== '' && product !== '' && (phone !== '' || email !== '') && (email === '' || isEmail(email));
      if (!valid) { status.textContent = MSG[L()].err; status.className = 'form-status err'; return; }

      status.textContent = MSG[L()].sending; status.className = 'form-status';
      if (submitBtn) submitBtn.disabled = true;

      fetch('quote.php', { method: 'POST', body: new FormData(form) })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (d) {
          if (d && d.ok) {
            status.textContent = MSG[L()].ok; status.className = 'form-status ok'; form.reset();
            setTimeout(close, 2600);
          } else {
            status.textContent = MSG[L()].err; status.className = 'form-status err';
          }
        })
        .catch(function () { status.textContent = MSG[L()].neterr; status.className = 'form-status err'; })
        .then(function () { if (submitBtn) submitBtn.disabled = false; });
    });
  }
})();
