/* emdatra service worker — offline support + static asset caching */
var CACHE = 'emdatra-v1';
var PRECACHE = [
  'css/style.css',
  'css/chat.css',
  'js/main.js',
  'js/chat.js',
  'js/quote.js',
  'js/track.js',
  'js/tools.js',
  'manifest.json',
  'assets/icons/icon-192.png'
];

self.addEventListener('install', function (e) {
  e.waitUntil(
    caches.open(CACHE).then(function (c) { return c.addAll(PRECACHE); }).then(function () {
      return self.skipWaiting();
    })
  );
});

self.addEventListener('activate', function (e) {
  e.waitUntil(
    caches.keys().then(function (keys) {
      return Promise.all(keys.filter(function (k) { return k !== CACHE; }).map(function (k) { return caches.delete(k); }));
    }).then(function () { return self.clients.claim(); })
  );
});

self.addEventListener('fetch', function (e) {
  var req = e.request;
  if (req.method !== 'GET') return;

  var url = new URL(req.url);
  if (url.origin !== self.location.origin) return;

  // Pages: network first so content stays fresh; cached copy only when offline.
  if (req.mode === 'navigate') {
    e.respondWith(
      fetch(req).then(function (res) {
        var copy = res.clone();
        caches.open(CACHE).then(function (c) { c.put(req, copy); });
        return res;
      }).catch(function () {
        return caches.match(req, { ignoreSearch: true }).then(function (hit) {
          return hit || caches.match('index.php', { ignoreSearch: true });
        });
      })
    );
    return;
  }

  // Static assets: cache first (they carry a version query when they change).
  var dest = req.destination;
  if (dest === 'style' || dest === 'script' || dest === 'image' || dest === 'font') {
    e.respondWith(
      caches.match(req).then(function (hit) {
        return hit || fetch(req).then(function (res) {
          if (res.ok) {
            var copy = res.clone();
            caches.open(CACHE).then(function (c) { c.put(req, copy); });
          }
          return res;
        });
      })
    );
  }
  // Everything else (chat polling, tracking, forms) goes straight to the network.
});
