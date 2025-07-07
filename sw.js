/**
 * MoodifyMe Service Worker
 * Provides offline functionality, caching, and push notifications
 */

const CACHE_NAME = 'moodifyme-v1.0.0';
const STATIC_CACHE_NAME = 'moodifyme-static-v1.0.0';
const DYNAMIC_CACHE_NAME = 'moodifyme-dynamic-v1.0.0';

// Files to cache for offline functionality
const STATIC_ASSETS = [
    '/MoodifyMe/',
    '/MoodifyMe/index.php',
    '/MoodifyMe/pages/dashboard.php',
    '/MoodifyMe/pages/mood_tracker.php',
    '/MoodifyMe/pages/messages.php',
    '/MoodifyMe/pages/community_posts.php',
    '/MoodifyMe/pages/profile.php',
    '/MoodifyMe/assets/css/bootstrap.min.css',
    '/MoodifyMe/assets/js/bootstrap.bundle.min.js',
    '/MoodifyMe/assets/js/voice-recorder.js',
    '/MoodifyMe/assets/js/message-notifications.js',
    '/MoodifyMe/assets/images/logo.png',
    '/MoodifyMe/manifest.json',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
    'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap'
];

// API endpoints that should be cached
const API_CACHE_PATTERNS = [
    '/MoodifyMe/api/get_mood_data.php',
    '/MoodifyMe/api/get_notifications.php',
    '/MoodifyMe/api/get_user_profile.php'
];

// Install event - cache static assets
self.addEventListener('install', event => {
    console.log('Service Worker: Installing...');
    
    event.waitUntil(
        caches.open(STATIC_CACHE_NAME)
            .then(cache => {
                console.log('Service Worker: Caching static assets');
                return cache.addAll(STATIC_ASSETS);
            })
            .then(() => {
                console.log('Service Worker: Static assets cached');
                return self.skipWaiting();
            })
            .catch(error => {
                console.error('Service Worker: Error caching static assets', error);
            })
    );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
    console.log('Service Worker: Activating...');
    
    event.waitUntil(
        caches.keys()
            .then(cacheNames => {
                return Promise.all(
                    cacheNames.map(cacheName => {
                        if (cacheName !== STATIC_CACHE_NAME && 
                            cacheName !== DYNAMIC_CACHE_NAME &&
                            cacheName !== CACHE_NAME) {
                            console.log('Service Worker: Deleting old cache', cacheName);
                            return caches.delete(cacheName);
                        }
                    })
                );
            })
            .then(() => {
                console.log('Service Worker: Activated');
                return self.clients.claim();
            })
    );
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);
    
    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }
    
    // Skip chrome-extension and other non-http requests
    if (!request.url.startsWith('http')) {
        return;
    }
    
    event.respondWith(
        caches.match(request)
            .then(cachedResponse => {
                // Return cached version if available
                if (cachedResponse) {
                    // For API requests, try to fetch fresh data in background
                    if (isApiRequest(request.url)) {
                        fetchAndCache(request);
                    }
                    return cachedResponse;
                }
                
                // Fetch from network and cache
                return fetchAndCache(request);
            })
            .catch(() => {
                // Network failed, try to serve offline page for navigation requests
                if (request.destination === 'document') {
                    return caches.match('/MoodifyMe/pages/offline.php') ||
                           caches.match('/MoodifyMe/');
                }
                
                // For other requests, return a generic offline response
                return new Response('Offline', {
                    status: 503,
                    statusText: 'Service Unavailable'
                });
            })
    );
});

// Helper function to fetch and cache requests
function fetchAndCache(request) {
    return fetch(request)
        .then(response => {
            // Don't cache error responses
            if (!response.ok) {
                return response;
            }
            
            // Clone response for caching
            const responseClone = response.clone();
            
            // Determine cache name
            const cacheName = isStaticAsset(request.url) ? 
                             STATIC_CACHE_NAME : 
                             DYNAMIC_CACHE_NAME;
            
            // Cache the response
            caches.open(cacheName)
                .then(cache => {
                    cache.put(request, responseClone);
                })
                .catch(error => {
                    console.error('Service Worker: Error caching response', error);
                });
            
            return response;
        });
}

// Helper function to check if URL is a static asset
function isStaticAsset(url) {
    return STATIC_ASSETS.some(asset => url.includes(asset)) ||
           url.includes('.css') ||
           url.includes('.js') ||
           url.includes('.png') ||
           url.includes('.jpg') ||
           url.includes('.jpeg') ||
           url.includes('.gif') ||
           url.includes('.svg') ||
           url.includes('.woff') ||
           url.includes('.woff2');
}

// Helper function to check if URL is an API request
function isApiRequest(url) {
    return url.includes('/api/') ||
           API_CACHE_PATTERNS.some(pattern => url.includes(pattern));
}

// Push notification event
self.addEventListener('push', event => {
    console.log('Service Worker: Push notification received');
    
    let notificationData = {
        title: 'MoodifyMe',
        body: 'You have a new notification',
        icon: '/MoodifyMe/assets/images/pwa/icon-192x192.png',
        badge: '/MoodifyMe/assets/images/pwa/badge-72x72.png',
        tag: 'moodifyme-notification',
        requireInteraction: false,
        actions: [
            {
                action: 'open',
                title: 'Open App',
                icon: '/MoodifyMe/assets/images/pwa/action-open.png'
            },
            {
                action: 'dismiss',
                title: 'Dismiss',
                icon: '/MoodifyMe/assets/images/pwa/action-dismiss.png'
            }
        ]
    };
    
    // Parse push data if available
    if (event.data) {
        try {
            const pushData = event.data.json();
            notificationData = { ...notificationData, ...pushData };
        } catch (error) {
            console.error('Service Worker: Error parsing push data', error);
        }
    }
    
    event.waitUntil(
        self.registration.showNotification(notificationData.title, notificationData)
    );
});

// Notification click event
self.addEventListener('notificationclick', event => {
    console.log('Service Worker: Notification clicked');
    
    event.notification.close();
    
    const action = event.action;
    const notificationData = event.notification.data || {};
    
    if (action === 'dismiss') {
        return;
    }
    
    // Determine URL to open
    let urlToOpen = '/MoodifyMe/';
    
    if (action === 'open' || !action) {
        if (notificationData.url) {
            urlToOpen = notificationData.url;
        } else if (notificationData.type === 'message') {
            urlToOpen = '/MoodifyMe/pages/messages.php';
        } else if (notificationData.type === 'mood_reminder') {
            urlToOpen = '/MoodifyMe/pages/mood_tracker.php';
        } else if (notificationData.type === 'community') {
            urlToOpen = '/MoodifyMe/pages/community_posts.php';
        }
    }
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clientList => {
                // Check if app is already open
                for (const client of clientList) {
                    if (client.url.includes('/MoodifyMe/') && 'focus' in client) {
                        client.focus();
                        client.navigate(urlToOpen);
                        return;
                    }
                }
                
                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Background sync event (for offline actions)
self.addEventListener('sync', event => {
    console.log('Service Worker: Background sync triggered', event.tag);
    
    if (event.tag === 'mood-sync') {
        event.waitUntil(syncMoodData());
    } else if (event.tag === 'message-sync') {
        event.waitUntil(syncMessages());
    }
});

// Helper function to sync mood data
function syncMoodData() {
    // Implementation for syncing mood data when back online
    return fetch('/MoodifyMe/api/sync_mood_data.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            console.log('Service Worker: Mood data synced successfully');
        }
    })
    .catch(error => {
        console.error('Service Worker: Error syncing mood data', error);
    });
}

// Helper function to sync messages
function syncMessages() {
    // Implementation for syncing messages when back online
    return fetch('/MoodifyMe/api/sync_messages.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            console.log('Service Worker: Messages synced successfully');
        }
    })
    .catch(error => {
        console.error('Service Worker: Error syncing messages', error);
    });
}
