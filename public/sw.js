const CACHE = 'sales-app-v1';

self.addEventListener('install', (e) => {
    self.skipWaiting();
});

self.addEventListener('activate', (e) => {
    e.waitUntil(self.clients.claim());
});

self.addEventListener('fetch', (e) => {
    if (e.request.method !== 'GET') return;

    const url = new URL(e.request.url);
    if (url.origin !== location.origin) return;

    const isStatic = url.pathname.includes('/assets/') ||
        url.pathname.includes('/uploads/') ||
        url.pathname.endsWith('.svg') ||
        url.pathname.endsWith('.png') ||
        url.pathname.endsWith('.css') ||
        url.pathname.endsWith('.js');

    if (!isStatic) return;

    e.respondWith(
        caches.open(CACHE).then(async (cache) => {
            const cached = await cache.match(e.request);
            const fetched = fetch(e.request)
                .then((res) => {
                    if (res.ok) cache.put(e.request, res.clone());
                    return res;
                })
                .catch(() => cached);
            return cached || fetched;
        })
    );
});