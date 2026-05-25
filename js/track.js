/* =========================================================
   emdatra — shipment tracking modal
   ========================================================= */
(function () {
  'use strict';
  var modal = document.getElementById('trackModal');
  if (!modal) return;
  var form = document.getElementById('trackForm');
  var input = document.getElementById('trackNo');
  var result = document.getElementById('trackResult');
  var lastFocus = null;

  var T = {
    ar: { status: 'الحالة الحالية', mode: 'وسيلة الشحن', eta: 'الوصول المتوقّع', searching: 'جارٍ البحث...', notfound: 'لم نعثر على شحنة بهذا الرقم. تأكد من الرقم أو تواصل معنا.', neterr: 'تعذّر الاتصال، حاول مرة أخرى.', timeline: 'مسار الشحنة', empty: 'لا توجد تحديثات بعد.' },
    en: { status: 'Current status', mode: 'Shipping mode', eta: 'Estimated arrival', searching: 'Searching...', notfound: 'No shipment found for this number. Check it or contact us.', neterr: 'Connection failed, please try again.', timeline: 'Shipment timeline', empty: 'No updates yet.' }
  };
  function lang() { return document.documentElement.lang === 'en' ? 'en' : 'ar'; }
  function t(k) { return (T[lang()] || T.ar)[k]; }
  function el(tag, cls, txt) { var n = document.createElement(tag); if (cls) n.className = cls; if (txt != null) n.textContent = txt; return n; }

  function open() {
    lastFocus = document.activeElement;
    modal.classList.add('open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    setTimeout(function () { input.focus(); }, 60);
  }
  function close() {
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  document.addEventListener('click', function (e) {
    var trigger = e.target.closest('[data-track]') || e.target.closest('a[href="#track"]');
    if (trigger) { e.preventDefault(); open(); return; }
    if (e.target.closest('[data-track-close]')) { e.preventDefault(); close(); }
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });

  function render(s) {
    result.innerHTML = '';
    var lg = lang();
    var statusTxt = lg === 'en' ? s.status_en : s.status_ar;
    var modeTxt = lg === 'en' ? s.mode_en : s.mode_ar;

    var sum = el('div', 'track-summary');
    var route = el('div', 'track-route');
    route.appendChild(el('span', null, s.origin || '—'));
    route.appendChild(el('i', 'track-route__arrow', '←'));
    route.appendChild(el('span', null, s.destination || '—'));
    sum.appendChild(route);
    var badge = el('div', 'track-badge', statusTxt);
    sum.appendChild(badge);
    var meta = el('div', 'track-meta');
    meta.appendChild(el('span', null, t('mode') + ': ' + modeTxt));
    if (s.eta) meta.appendChild(el('span', null, t('eta') + ': ' + s.eta));
    sum.appendChild(meta);
    result.appendChild(sum);

    var h = el('div', 'track-timeline__title', t('timeline'));
    result.appendChild(h);

    if (!s.events || !s.events.length) {
      result.appendChild(el('p', 'track-empty', t('empty')));
      return;
    }
    var ul = el('ul', 'track-timeline');
    s.events.forEach(function (ev, i) {
      var li = el('li', 'track-ev' + (i === 0 ? ' is-latest' : ''));
      li.appendChild(el('span', 'track-ev__dot'));
      var bd = el('div', 'track-ev__body');
      bd.appendChild(el('b', null, lg === 'en' ? ev.status_en : ev.status_ar));
      bd.appendChild(el('span', 'track-ev__time', ev.at));
      var sub = [ev.location, ev.note].filter(Boolean).join(' · ');
      if (sub) bd.appendChild(el('div', 'track-ev__sub', sub));
      li.appendChild(bd);
      ul.appendChild(li);
    });
    result.appendChild(ul);
  }

  if (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var no = input.value.trim();
      if (no === '') return;
      result.innerHTML = '';
      result.appendChild(el('p', 'track-empty', t('searching')));
      fetch('track.php?no=' + encodeURIComponent(no))
        .then(function (r) { return r.json(); })
        .then(function (d) {
          if (d && d.ok) render(d.shipment);
          else { result.innerHTML = ''; result.appendChild(el('p', 'track-notfound', t('notfound'))); }
        })
        .catch(function () { result.innerHTML = ''; result.appendChild(el('p', 'track-notfound', t('neterr'))); });
    });
  }
})();
