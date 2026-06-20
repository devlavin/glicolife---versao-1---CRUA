const CACHE_NAME = 'glicolife-v2';

// Arquivos estáticos para cache (funciona offline)
const STATIC_ASSETS = [
  '/glicolife/',
  '/glicolife/index.php',
  '/glicolife/login.php',
  '/glicolife/css/index.css',
  '/glicolife/img/gota-mascote.png',
  '/glicolife/img/icon-192.png',
  '/glicolife/img/icon-512.png',
];

// ─── Instalação: faz cache dos arquivos estáticos ───────────────────────────
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => cache.addAll(STATIC_ASSETS))
  );
  self.skipWaiting();
});

// ─── Ativação: limpa caches antigos ─────────────────────────────────────────
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys =>
      Promise.all(
        keys
          .filter(key => key !== CACHE_NAME)
          .map(key => caches.delete(key))
      )
    )
  );
  self.clients.claim();
});

// ─── Fetch: network-first para PHP, cache-first para estáticos ──────────────
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Ignora requisições de outros domínios
  if (url.origin !== location.origin) return;

  // Para arquivos PHP (páginas dinâmicas): tenta rede primeiro
  if (url.pathname.endsWith('.php') || url.pathname === '/') {
    event.respondWith(
      fetch(event.request)
        .catch(() => caches.match('/glicolife/index.php')) // offline: mostra index
    );
    return;
  }

  // Para CSS, JS, imagens: cache primeiro
  event.respondWith(
    caches.match(event.request).then(cached => {
      return cached || fetch(event.request).then(response => {
        // Salva no cache para próxima vez
        if (response.ok) {
          const clone = response.clone();
          caches.open(CACHE_NAME).then(cache => cache.put(event.request, clone));
        }
        return response;
      });
    })
  );
});