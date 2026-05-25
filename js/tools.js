/* =========================================================
   emdatra — shipping tools: freight estimator + Incoterms guide
   ========================================================= */
(function () {
  'use strict';
  function lang() { return document.documentElement.lang === 'en' ? 'en' : 'ar'; }
  function el(tag, cls, txt) { var n = document.createElement(tag); if (cls) n.className = cls; if (txt != null) n.textContent = txt; return n; }

  function modalToggle(modal, openSel, closeSel) {
    if (!modal) return { open: function () {}, close: function () {} };
    var lastFocus = null;
    function open() {
      lastFocus = document.activeElement;
      modal.classList.add('open'); modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
    function close() {
      modal.classList.remove('open'); modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      if (lastFocus && lastFocus.focus) lastFocus.focus();
    }
    document.addEventListener('click', function (e) {
      if (e.target.closest(openSel)) { e.preventDefault(); open(); return; }
      if (e.target.closest(closeSel)) { e.preventDefault(); close(); }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && modal.classList.contains('open')) close(); });
    return { open: open, close: close };
  }

  /* ---------------- Freight estimator ---------------- */
  (function () {
    var modal = document.getElementById('freightModal');
    if (!modal) return;
    var ctl = modalToggle(modal, '[data-freight], a[href="#freight"]', '[data-freight-close]');
    var form = document.getElementById('freightForm');
    var out = document.getElementById('freightResult');

    var T = {
      ar: { gross: 'الوزن الإجمالي', volume: 'الحجم الكلي', charge: 'الوزن القابل للاحتساب', tons: 'الأطنان المحسوبة (وزن/حجم)', basis: 'يُحتسب الشحن على الأكبر بين الوزن والحجم.', cbm: 'م³', kg: 'كجم', ton: 'طن', need: 'أدخل الأبعاد والوزن لحساب التقدير.', cta: 'اطلب عرض سعر دقيق', note: 'هذه أرقام إرشادية لمساعدتك على فهم أساس التسعير؛ للسعر النهائي اطلب عرض سعر.' },
      en: { gross: 'Gross weight', volume: 'Total volume', charge: 'Chargeable weight', tons: 'Revenue tons (weight/volume)', basis: 'Freight is charged on the greater of weight or volume.', cbm: 'm³', kg: 'kg', ton: 't', need: 'Enter dimensions and weight to estimate.', cta: 'Request an exact quote', note: 'These are indicative figures to help you understand the pricing basis; request a quote for the final price.' }
    };
    function t(k) { return (T[lang()] || T.ar)[k]; }
    function num(id) { var v = parseFloat(document.getElementById(id).value); return isFinite(v) && v > 0 ? v : 0; }

    function row(label, value) {
      var r = el('div', 'freight-row');
      r.appendChild(el('span', null, label));
      r.appendChild(el('b', null, value));
      return r;
    }

    if (form) form.addEventListener('submit', function (e) {
      e.preventDefault();
      var mode = document.getElementById('frMode').value;
      var gross = num('frWeight');
      var qty = Math.max(1, num('frQty') || 1);
      var L = num('frL'), W = num('frW'), H = num('frH');
      var cbmEach = (L * W * H) / 1000000;
      var volume = cbmEach * qty;
      out.innerHTML = '';

      if (gross <= 0 && volume <= 0) { out.appendChild(el('p', 'track-empty', t('need'))); return; }

      var box = el('div', 'track-summary');
      box.appendChild(row(t('gross'), (gross || 0).toLocaleString() + ' ' + t('kg')));
      box.appendChild(row(t('volume'), volume.toFixed(3) + ' ' + t('cbm')));

      if (mode === 'air') {
        var volW = volume * 166.67;
        var charge = Math.max(gross, volW);
        box.appendChild(row(t('charge'), Math.ceil(charge).toLocaleString() + ' ' + t('kg')));
      } else {
        var tons = Math.max(gross / 1000, volume);
        box.appendChild(row(t('tons'), tons.toFixed(2) + ' ' + t('ton')));
      }
      out.appendChild(box);
      out.appendChild(el('p', 'freight-note', t('basis') + ' ' + t('note')));

      var cta = el('button', 'btn btn--primary btn--block', t('cta'));
      cta.type = 'button';
      cta.addEventListener('click', function () { ctl.close(); if (window.emdOpenQuote) window.emdOpenQuote(); });
      out.appendChild(cta);
    });
  })();

  /* ---------------- Incoterms guide ---------------- */
  (function () {
    var modal = document.getElementById('incotermsModal');
    if (!modal) return;
    modalToggle(modal, '[data-incoterms], a[href="#incoterms"]', '[data-incoterms-close]');
    var list = document.getElementById('incoList');
    if (!list) return;

    var DATA = [
      { c: 'EXW', ar: ['تسليم أرض المصنع', 'يجهّز البائع البضاعة في مقرّه، ويتحمّل المشتري كل التكاليف والمخاطر من تلك النقطة (نقل، تصدير، شحن، استيراد).'], en: ['Ex Works', 'Seller makes goods available at their premises; buyer bears all cost and risk from there (transport, export, freight, import).'] },
      { c: 'FCA', ar: ['التسليم إلى الناقل', 'يسلّم البائع البضاعة للناقل في مكان متفق عليه ويخلّصها للتصدير؛ تنتقل المخاطر عند التسليم.'], en: ['Free Carrier', 'Seller delivers goods to the carrier at an agreed place and clears them for export; risk transfers at handover.'] },
      { c: 'FAS', ar: ['التسليم بجانب السفينة', 'يسلّم البائع البضاعة بجانب السفينة في ميناء الشحن (بحري فقط).'], en: ['Free Alongside Ship', 'Seller delivers goods alongside the vessel at the port of shipment (sea only).'] },
      { c: 'FOB', ar: ['التسليم على ظهر السفينة', 'تنتقل المخاطر عند تحميل البضاعة على ظهر السفينة في ميناء الشحن (بحري فقط).'], en: ['Free On Board', 'Risk transfers once goods are loaded on board the vessel at the port of shipment (sea only).'] },
      { c: 'CFR', ar: ['التكلفة وأجرة الشحن', 'يدفع البائع الشحن حتى ميناء الوصول، لكن المخاطر تنتقل عند التحميل (بحري فقط).'], en: ['Cost and Freight', 'Seller pays freight to the destination port, but risk transfers on loading (sea only).'] },
      { c: 'CIF', ar: ['التكلفة والتأمين وأجرة الشحن', 'مثل CFR مع تأمين بحري بحدّ أدنى يدفعه البائع (بحري فقط).'], en: ['Cost, Insurance & Freight', 'Like CFR plus minimum marine insurance paid by the seller (sea only).'] },
      { c: 'CPT', ar: ['أجرة النقل مدفوعة حتى', 'يدفع البائع النقل حتى الوجهة، وتنتقل المخاطر عند تسليم أول ناقل.'], en: ['Carriage Paid To', 'Seller pays carriage to the destination; risk transfers when handed to the first carrier.'] },
      { c: 'CIP', ar: ['النقل والتأمين مدفوعان حتى', 'مثل CPT مع تأمين شامل يدفعه البائع.'], en: ['Carriage & Insurance Paid To', 'Like CPT plus comprehensive insurance paid by the seller.'] },
      { c: 'DAP', ar: ['التسليم في المكان', 'يتحمّل البائع كل شيء حتى وصول البضاعة إلى المكان المتفق (دون تفريغ ودون رسوم استيراد).'], en: ['Delivered At Place', 'Seller bears everything until goods arrive at the agreed place (not unloaded, import duties excluded).'] },
      { c: 'DPU', ar: ['التسليم في المكان مفرّغًا', 'مثل DAP لكن يشمل تفريغ البضاعة في الوجهة.'], en: ['Delivered at Place Unloaded', 'Like DAP but includes unloading the goods at the destination.'] },
      { c: 'DDP', ar: ['التسليم خالص الرسوم', 'يتحمّل البائع كل التكاليف حتى التسليم النهائي بما فيها التخليص ورسوم الاستيراد.'], en: ['Delivered Duty Paid', 'Seller bears all costs to final delivery, including clearance and import duties.'] }
    ];

    function build() {
      var lg = lang();
      list.innerHTML = '';
      DATA.forEach(function (it) {
        var item = el('div', 'ico-item');
        var head = el('button', 'ico-item__head'); head.type = 'button';
        head.appendChild(el('span', 'ico-item__code', it.c));
        head.appendChild(el('span', 'ico-item__name', (lg === 'en' ? it.en : it.ar)[0]));
        var body = el('div', 'ico-item__body', (lg === 'en' ? it.en : it.ar)[1]);
        head.addEventListener('click', function () { item.classList.toggle('open'); });
        item.appendChild(head); item.appendChild(body);
        list.appendChild(item);
      });
    }
    build();
    var langBtn = document.getElementById('langToggle');
    if (langBtn) langBtn.addEventListener('click', function () { setTimeout(build, 0); });
  })();
})();
