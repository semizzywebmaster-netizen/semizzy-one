// SEMIZZY ONE — Service Worker v2.0.0
const CACHE_NAME = 'semizzy-one-v2.0.0';
const STATIC_CACHE = 'semizzy-static-v2.0.0';
const DYNAMIC_CACHE = 'semizzy-dynamic-v2.0.0';
const OFFLINE_URL = '/offline.html';

// Assets to pre-cache (app shell)
const PRECACHE_URLS = [
    '/',
    '/offline.html',
    '/manifest.webmanifest',
];

// Install event — pre-cache app shell
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => cache.addAll(PRECACHE_URLS))
            .then(() => self.skipWaiting())
    );
});

// Activate event — clean old caches
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((name) => name !== STATIC_CACHE && name !== DYNAMIC_CACHE && name !== CACHE_NAME)
                    .map((name) => caches.delete(name))
            );
        }).then(() => self.clients.claim())
    );
});

// Fetch event — network-first for API, cache-first for assets
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') return;

    // Skip chrome-extension and other non-http
    if (!url.protocol.startsWith('http')) return;

    // API requests — network only (never cache sensitive API responses)
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request).catch(() => {
                return new Response(
                    JSON.stringify({
                        success: false,
                        message: 'You are offline. This operation requires an internet connection.',
                        offline: true,
                    }),
                    {
                        status: 503,
                        headers: { 'Content-Type': 'application/json' },
                    }
                );
            })
        );
        return;
    }

    // Navigation requests — network first, fallback to offline page
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache successful navigation responses
                    const responseClone = response.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                    return response;
                })
                .catch(() => {
                    return caches.match(request).then((cachedResponse) => {
                        return cachedResponse || caches.match(OFFLINE_URL);
                    });
                })
        );
        return;
    }

    // Static assets — cache first, then network
    event.respondWith(
        caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
                // Return cached and update in background
                fetch(request).then((networkResponse) => {
                    caches.open(STATIC_CACHE).then((cache) => {
                        cache.put(request, networkResponse);
                    });
                }).catch(() => {});
                return cachedResponse;
            }

            return fetch(request).then((networkResponse) => {
                // Cache new static assets
                if (networkResponse.ok && (
                    url.pathname.match(/\.(js|css|png|jpg|jpeg|gif|svg|ico|woff|woff2|ttf|eot)$/)
                )) {
                    const responseClone = networkResponse.clone();
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }
                return networkResponse;
            }).catch(() => {
                // Return fallback for images
                if (request.destination === 'image') {
                    return new Response(
                        '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="200"><text x="50%" y="50%" text-anchor="middle" fill="#64748B">Offline</text></svg>',
                        { headers: { 'Content-Type': 'image/svg+xml' } }
                    );
                }
                return new Response('', { status: 503 });
            });
        })
    );
});

// Background sync support
self.addEventListener('sync', (event) => {
    if (event.tag === 'semizzy-sync') {
        event.waitUntil(doSync());
    }
});

async function doSync() {
    // Notify all clients that sync is starting
    const clients = await self.clients.matchAll();
    clients.forEach((client) => {
        client.postMessage({ type: 'SYNC_START' });
    });

    try {
        // Sync pending operations from IndexedDB
        // This will be implemented when offline data is added
        clients.forEach((client) => {
            client.postMessage({ type: 'SYNC_COMPLETE' });
        });
    } catch (error) {
        clients.forEach((client) => {
            client.postMessage({ type: 'SYNC_ERROR', error: error.message });
        });
    }
}

// Push notification support
self.addEventListener('push', (event) => {
    if (!event.data) return;

    const data = event.data.json();
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: { url: data.url || '/' },
        actions: data.actions || [],
    };

    event.waitUntil(
        self.registration.showNotification(data.title || 'SEMIZZY ONE', options)
    );
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    event.waitUntil(
        self.clients.openWindow(event.notification.data.url)
    );
});