/* =========================================================
   emdatra — live chat widget (visitor side)
   Self-contained: builds its own UI, talks to chat.php,
   polls for admin replies, and follows the site language.
   ========================================================= */
(function () {
  'use strict';

  var TOKEN_KEY = 'emdatra_chat_token';
  var ENDPOINT = 'chat.php';
  var POLL_MS = 6000;

  var T = {
    ar: {
      title: 'الدعم والمبيعات', status: 'متصلون الآن',
      lead: 'أهلاً بك! اترك اسمك وطريقة للتواصل ورسالتك، وسنرد عليك هنا مباشرةً.',
      name: 'الاسم', namePh: 'اسمك الكريم',
      email: 'البريد الإلكتروني', emailPh: 'example@email.com',
      phone: 'رقم الهاتف', phonePh: '+966 5X XXX XXXX',
      contactHint: 'أدخل البريد أو الهاتف (أحدهما يكفي)',
      msg: 'رسالتك', msgPh: 'كيف يمكننا مساعدتك؟',
      start: 'بدء المحادثة', typePh: 'اكتب رسالتك...',
      errName: 'الرجاء إدخال اسمك.', errContact: 'أدخل البريد الإلكتروني أو رقم الهاتف.',
      errEmail: 'البريد الإلكتروني غير صحيح.', errMsg: 'الرجاء كتابة رسالتك.',
      errNet: 'تعذّر الاتصال. حاول مرة أخرى.',
      welcome: 'بدأت محادثتك! سنرد عليك في أقرب وقت — يمكنك متابعة الردود هنا.'
    },
    en: {
      title: 'Support & Sales', status: 'Online now',
      lead: 'Welcome! Leave your name, a way to reach you and your message — we will reply right here.',
      name: 'Name', namePh: 'Your name',
      email: 'Email', emailPh: 'example@email.com',
      phone: 'Phone', phonePh: '+966 5X XXX XXXX',
      contactHint: 'Enter an email or a phone (either is fine)',
      msg: 'Your message', msgPh: 'How can we help you?',
      start: 'Start chat', typePh: 'Type your message...',
      errName: 'Please enter your name.', errContact: 'Enter an email or a phone number.',
      errEmail: 'That email looks invalid.', errMsg: 'Please write your message.',
      errNet: 'Connection failed. Please try again.',
      welcome: 'Your chat has started! We will reply soon — follow replies right here.'
    }
  };
  function lang() { return document.documentElement.lang === 'en' ? 'en' : 'ar'; }
  function t(k) { return (T[lang()] || T.ar)[k]; }

  var ICON = {
    chat: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.5 8.5 0 0 1-12.6 7.5L3 21l2-5.4A8.5 8.5 0 1 1 21 11.5Z"/><path d="M8.5 12h7M8.5 8.5h7"/></svg>',
    close: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>',
    send: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg>'
  };

  var token = null;
  try { token = localStorage.getItem(TOKEN_KEY) || null; } catch (e) {}
  var rendered = {}, lastId = 0, isOpen = false, busy = false, timer = null;

  /* ---------- build UI ---------- */
  function el(tag, cls, html) {
    var n = document.createElement(tag);
    if (cls) n.className = cls;
    if (html != null) n.innerHTML = html;
    return n;
  }

  var fab = el('button', 'chat-fab', '<span class="chat-fab__open">' + ICON.chat + '</span><span class="chat-fab__close">' + ICON.close + '</span><span class="chat-fab__badge" id="chatBadge"></span>');
  fab.id = 'chatFab';
  fab.type = 'button';
  fab.setAttribute('aria-label', 'محادثة');

  var panel = el('div', 'chat-panel');
  panel.id = 'chatPanel';

  var head = el('div', 'chat-head',
    '<div class="chat-head__av"><img src="assets/logo.png" alt="emdatra"></div>' +
    '<div class="chat-head__id"><div class="chat-head__title" id="chatTitle"></div><div class="chat-head__status" id="chatStatus"></div></div>' +
    '<button class="chat-head__close" id="chatClose" type="button" aria-label="إغلاق">' + ICON.close + '</button>');

  var intro = el('div', 'chat-intro');
  intro.id = 'chatIntro';
  intro.innerHTML =
    '<p class="chat-intro__lead" id="ciLead"></p>' +
    '<div class="chat-field" id="ciNameF"><label id="ciNameL"></label><input type="text" id="ciName" autocomplete="name"></div>' +
    '<div class="chat-field" id="ciEmailF"><label id="ciEmailL"></label><input type="email" id="ciEmail" autocomplete="email" dir="ltr"></div>' +
    '<div class="chat-field" id="ciPhoneF"><label id="ciPhoneL"></label><input type="tel" id="ciPhone" autocomplete="tel" dir="ltr"></div>' +
    '<div class="chat-field" id="ciMsgF"><label id="ciMsgL"></label><textarea id="ciMsg" rows="3"></textarea></div>' +
    '<button class="chat-intro__btn" id="ciStart" type="button"></button>' +
    '<div class="chat-intro__err" id="ciErr"></div>';

  var body = el('div', 'chat-body');
  body.id = 'chatBody';
  body.style.display = 'none';

  var foot = el('div', 'chat-foot');
  foot.id = 'chatFoot';
  foot.style.display = 'none';
  foot.innerHTML = '<textarea id="chatInput" rows="1"></textarea><button class="chat-send" id="chatSend" type="button" aria-label="إرسال">' + ICON.send + '</button>';

  panel.appendChild(head);
  panel.appendChild(intro);
  panel.appendChild(body);
  panel.appendChild(foot);
  document.body.appendChild(fab);
  document.body.appendChild(panel);

  var $ = function (id) { return document.getElementById(id); };
  var badge = $('chatBadge');

  /* ---------- language labels ---------- */
  function applyLabels() {
    $('chatTitle').textContent = t('title');
    $('chatStatus').textContent = t('status');
    $('ciLead').textContent = t('lead');
    $('ciNameL').textContent = t('name');
    $('ciName').setAttribute('placeholder', t('namePh'));
    $('ciEmailL').textContent = t('email');
    $('ciEmail').setAttribute('placeholder', t('emailPh'));
    $('ciPhoneL').textContent = t('phone');
    $('ciPhone').setAttribute('placeholder', t('phonePh'));
    $('ciMsgL').textContent = t('msg');
    $('ciMsg').setAttribute('placeholder', t('msgPh'));
    $('ciStart').textContent = t('start');
    $('chatInput').setAttribute('placeholder', t('typePh'));
  }
  applyLabels();
  var langBtn = $('langToggle');
  if (langBtn) langBtn.addEventListener('click', function () { setTimeout(applyLabels, 0); });

  /* ---------- helpers ---------- */
  function scrollBottom() { body.scrollTop = body.scrollHeight; }

  function addMsg(m) {
    if (!m || rendered[m.id]) return;
    rendered[m.id] = true;
    if (m.id > lastId) lastId = m.id;
    var d = el('div', 'chat-msg chat-msg--' + (m.sender === 'admin' ? 'admin' : 'user'));
    d.appendChild(document.createTextNode(m.body));
    var tm = el('span', 'chat-msg__time');
    tm.textContent = m.time || '';
    d.appendChild(tm);
    body.appendChild(d);
    scrollBottom();
  }

  function note(text) {
    var n = el('div', 'chat-note');
    n.textContent = text;
    body.appendChild(n);
    scrollBottom();
  }

  function setBadge(n) {
    if (n > 0 && !isOpen) { badge.textContent = n > 9 ? '9+' : n; badge.classList.add('show'); }
    else { badge.classList.remove('show'); }
  }

  function showChatView() {
    intro.style.display = 'none';
    body.style.display = 'flex';
    foot.style.display = 'flex';
  }

  function req(params, isPost) {
    var url = ENDPOINT + (isPost ? '' : '?' + params);
    var opt = { method: isPost ? 'POST' : 'GET' };
    if (isPost) { opt.headers = { 'Content-Type': 'application/x-www-form-urlencoded' }; opt.body = params; }
    return fetch(url, opt).then(function (r) { return r.json(); });
  }
  function qs(o) {
    return Object.keys(o).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(o[k]); }).join('&');
  }

  /* ---------- actions ---------- */
  function start() {
    var name = $('ciName').value.trim();
    var email = $('ciEmail').value.trim();
    var phone = $('ciPhone').value.trim();
    var msg = $('ciMsg').value.trim();
    ['ciNameF', 'ciEmailF', 'ciPhoneF', 'ciMsgF'].forEach(function (f) { $(f).classList.remove('invalid'); });
    var err = $('ciErr'); err.textContent = '';

    if (name === '') { $('ciNameF').classList.add('invalid'); err.textContent = t('errName'); return; }
    if (email === '' && phone === '') { $('ciEmailF').classList.add('invalid'); $('ciPhoneF').classList.add('invalid'); err.textContent = t('errContact'); return; }
    if (email !== '' && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) { $('ciEmailF').classList.add('invalid'); err.textContent = t('errEmail'); return; }
    if (msg === '') { $('ciMsgF').classList.add('invalid'); err.textContent = t('errMsg'); return; }

    var btn = $('ciStart'); btn.disabled = true;
    req(qs({ action: 'start', name: name, email: email, phone: phone, message: msg }), true)
      .then(function (d) {
        if (d && d.ok) {
          token = d.token;
          try { localStorage.setItem(TOKEN_KEY, token); } catch (e) {}
          showChatView();
          (d.messages || []).forEach(addMsg);
          note(t('welcome'));
          startPolling();
        } else { err.textContent = t('errNet'); btn.disabled = false; }
      })
      .catch(function () { err.textContent = t('errNet'); btn.disabled = false; });
  }

  function send() {
    var input = $('chatInput');
    var v = input.value.trim();
    if (v === '' || busy || !token) return;
    busy = true; $('chatSend').disabled = true;
    req(qs({ action: 'send', token: token, message: v }), true)
      .then(function (d) {
        busy = false; $('chatSend').disabled = false;
        if (d && d.ok && d.message) { addMsg(d.message); input.value = ''; input.style.height = 'auto'; input.focus(); }
      })
      .catch(function () { busy = false; $('chatSend').disabled = false; });
  }

  function poll() {
    if (!token || document.hidden) return;
    req(qs({ action: 'poll', token: token, after: lastId, seen: isOpen ? 1 : 0 }), false)
      .then(function (d) {
        if (!d || !d.ok) return;
        (d.messages || []).forEach(addMsg);
        setBadge(d.unread || 0);
      })
      .catch(function () {});
  }

  function startPolling() {
    if (timer) clearInterval(timer);
    poll();
    timer = setInterval(poll, POLL_MS);
  }

  /* ---------- open / close ---------- */
  function openPanel() {
    isOpen = true;
    panel.classList.add('is-open');
    fab.classList.add('is-open');
    setBadge(0);
    if (token) { showChatView(); poll(); setTimeout(function () { $('chatInput').focus(); }, 250); }
    else { setTimeout(function () { $('ciName').focus(); }, 250); }
  }
  function closePanel() {
    isOpen = false;
    panel.classList.remove('is-open');
    fab.classList.remove('is-open');
  }

  fab.addEventListener('click', function () { isOpen ? closePanel() : openPanel(); });
  $('chatClose').addEventListener('click', closePanel);
  $('ciStart').addEventListener('click', start);
  $('chatSend').addEventListener('click', send);

  var input = $('chatInput');
  input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
  });
  input.addEventListener('input', function () { input.style.height = 'auto'; input.style.height = Math.min(input.scrollHeight, 110) + 'px'; });

  document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });

  /* ---------- resume existing conversation ---------- */
  if (token) { showChatView(); startPolling(); }
})();
