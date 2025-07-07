/**
 * Progressive Web App (PWA) Functionality
 * Handles service worker registration, installation prompts, and offline features
 */

class PWAManager {
    constructor() {
        this.deferredPrompt = null;
        this.isInstalled = false;
        this.isOnline = navigator.onLine;
        this.serviceWorkerRegistration = null;
        
        this.init();
    }
    
    async init() {
        // Check if app is already installed
        this.checkInstallationStatus();
        
        // Register service worker
        await this.registerServiceWorker();
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Initialize push notifications
        this.initializePushNotifications();
        
        // Show installation prompt if appropriate
        this.handleInstallPrompt();
        
        // Update online/offline status
        this.updateOnlineStatus();
    }
    
    checkInstallationStatus() {
        // Check if running in standalone mode (installed)
        this.isInstalled = window.matchMedia('(display-mode: standalone)').matches ||
                          window.navigator.standalone === true;
        
        if (this.isInstalled) {
            console.log('PWA: App is installed');
            this.hideInstallPrompt();
        }
    }
    
    async registerServiceWorker() {
        if ('serviceWorker' in navigator) {
            try {
                this.serviceWorkerRegistration = await navigator.serviceWorker.register('/MoodifyMe/sw.js', {
                    scope: '/MoodifyMe/'
                });
                
                console.log('PWA: Service Worker registered successfully');
                
                // Handle service worker updates
                this.serviceWorkerRegistration.addEventListener('updatefound', () => {
                    const newWorker = this.serviceWorkerRegistration.installing;
                    newWorker.addEventListener('statechange', () => {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            this.showUpdateAvailable();
                        }
                    });
                });
                
            } catch (error) {
                console.error('PWA: Service Worker registration failed:', error);
            }
        }
    }
    
    setupEventListeners() {
        // Install prompt event
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.showInstallPrompt();
        });
        
        // App installed event
        window.addEventListener('appinstalled', () => {
            console.log('PWA: App was installed');
            this.isInstalled = true;
            this.hideInstallPrompt();
            this.showInstallSuccess();
        });
        
        // Online/offline events
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.updateOnlineStatus();
            this.syncOfflineData();
        });
        
        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.updateOnlineStatus();
        });
        
        // Visibility change (for background sync)
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.isOnline) {
                this.syncOfflineData();
            }
        });
    }
    
    showInstallPrompt() {
        if (this.isInstalled || !this.deferredPrompt) return;
        
        // Create install prompt UI
        const installBanner = document.createElement('div');
        installBanner.id = 'pwa-install-banner';
        installBanner.className = 'pwa-install-banner';
        installBanner.innerHTML = `
            <div class="install-content">
                <div class="install-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <div class="install-text">
                    <h6>Install MoodifyMe</h6>
                    <p>Get the full app experience with offline access</p>
                </div>
                <div class="install-actions">
                    <button id="pwa-install-btn" class="btn btn-primary btn-sm">Install</button>
                    <button id="pwa-dismiss-btn" class="btn btn-outline-secondary btn-sm">Later</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(installBanner);
        
        // Add event listeners
        document.getElementById('pwa-install-btn').addEventListener('click', () => {
            this.installApp();
        });
        
        document.getElementById('pwa-dismiss-btn').addEventListener('click', () => {
            this.hideInstallPrompt();
        });
        
        // Auto-hide after 10 seconds
        setTimeout(() => {
            this.hideInstallPrompt();
        }, 10000);
    }
    
    hideInstallPrompt() {
        const banner = document.getElementById('pwa-install-banner');
        if (banner) {
            banner.remove();
        }
    }
    
    async installApp() {
        if (!this.deferredPrompt) return;
        
        try {
            this.deferredPrompt.prompt();
            const { outcome } = await this.deferredPrompt.userChoice;
            
            if (outcome === 'accepted') {
                console.log('PWA: User accepted the install prompt');
            } else {
                console.log('PWA: User dismissed the install prompt');
            }
            
            this.deferredPrompt = null;
            this.hideInstallPrompt();
            
        } catch (error) {
            console.error('PWA: Error during installation:', error);
        }
    }
    
    showInstallSuccess() {
        // Show success message
        const successToast = document.createElement('div');
        successToast.className = 'toast-notification success';
        successToast.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <span>MoodifyMe installed successfully!</span>
        `;
        
        document.body.appendChild(successToast);
        
        setTimeout(() => {
            successToast.remove();
        }, 3000);
    }
    
    showUpdateAvailable() {
        // Show update notification
        const updateBanner = document.createElement('div');
        updateBanner.id = 'pwa-update-banner';
        updateBanner.className = 'pwa-update-banner';
        updateBanner.innerHTML = `
            <div class="update-content">
                <i class="fas fa-download"></i>
                <span>A new version is available</span>
                <button id="pwa-update-btn" class="btn btn-primary btn-sm">Update</button>
            </div>
        `;
        
        document.body.appendChild(updateBanner);
        
        document.getElementById('pwa-update-btn').addEventListener('click', () => {
            this.updateApp();
        });
    }
    
    updateApp() {
        if (this.serviceWorkerRegistration && this.serviceWorkerRegistration.waiting) {
            this.serviceWorkerRegistration.waiting.postMessage({ action: 'skipWaiting' });
            window.location.reload();
        }
    }
    
    updateOnlineStatus() {
        // Update UI based on online/offline status
        const statusIndicator = document.getElementById('online-status');
        if (statusIndicator) {
            statusIndicator.className = this.isOnline ? 'online' : 'offline';
            statusIndicator.textContent = this.isOnline ? 'Online' : 'Offline';
        }
        
        // Show/hide offline indicator
        const offlineIndicator = document.getElementById('offline-indicator');
        if (offlineIndicator) {
            offlineIndicator.style.display = this.isOnline ? 'none' : 'block';
        }
        
        // Add offline class to body
        document.body.classList.toggle('offline', !this.isOnline);
    }
    
    async initializePushNotifications() {
        if (!('Notification' in window) || !this.serviceWorkerRegistration) {
            return;
        }
        
        // Request notification permission
        if (Notification.permission === 'default') {
            const permission = await Notification.requestPermission();
            console.log('PWA: Notification permission:', permission);
        }
        
        if (Notification.permission === 'granted') {
            this.subscribeToPushNotifications();
        }
    }
    
    async subscribeToPushNotifications() {
        try {
            // Generate VAPID keys (you'll need to implement this on your server)
            const vapidPublicKey = 'YOUR_VAPID_PUBLIC_KEY'; // Replace with actual key
            
            const subscription = await this.serviceWorkerRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.urlBase64ToUint8Array(vapidPublicKey)
            });
            
            // Send subscription to server
            await fetch('/MoodifyMe/api/save_push_subscription.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(subscription)
            });
            
            console.log('PWA: Push notification subscription saved');
            
        } catch (error) {
            console.error('PWA: Error subscribing to push notifications:', error);
        }
    }
    
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/-/g, '+')
            .replace(/_/g, '/');
        
        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);
        
        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }
    
    async syncOfflineData() {
        if (!this.serviceWorkerRegistration || !this.isOnline) return;
        
        try {
            // Trigger background sync for offline data
            await this.serviceWorkerRegistration.sync.register('mood-sync');
            await this.serviceWorkerRegistration.sync.register('message-sync');
            
            console.log('PWA: Background sync registered');
        } catch (error) {
            console.error('PWA: Error registering background sync:', error);
        }
    }
    
    // Public methods for app integration
    showOfflineMessage(message = 'You are currently offline. Some features may be limited.') {
        const offlineToast = document.createElement('div');
        offlineToast.className = 'toast-notification warning';
        offlineToast.innerHTML = `
            <i class="fas fa-wifi"></i>
            <span>${message}</span>
        `;
        
        document.body.appendChild(offlineToast);
        
        setTimeout(() => {
            offlineToast.remove();
        }, 5000);
    }
    
    cacheUserData(data) {
        // Cache user data for offline access
        if ('caches' in window) {
            caches.open('moodifyme-user-data').then(cache => {
                cache.put('/user-data', new Response(JSON.stringify(data)));
            });
        }
    }
    
    async getCachedUserData() {
        if ('caches' in window) {
            const cache = await caches.open('moodifyme-user-data');
            const response = await cache.match('/user-data');
            if (response) {
                return await response.json();
            }
        }
        return null;
    }
}

// Initialize PWA when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.pwaManager = new PWAManager();
});

// Export for use in other scripts
window.PWAManager = PWAManager;

// Add PWA styles to document
const pwaStyles = `
<style>
/* PWA Install Banner */
.pwa-install-banner {
    position: fixed;
    bottom: 20px;
    left: 20px;
    right: 20px;
    background: linear-gradient(135deg, #007bff, #0056b3);
    color: white;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 123, 255, 0.3);
    z-index: 1050;
    animation: slideUp 0.3s ease-out;
}

.install-content {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    gap: 15px;
}

.install-icon {
    font-size: 1.5rem;
    opacity: 0.9;
}

.install-text {
    flex: 1;
}

.install-text h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.install-text p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.install-actions {
    display: flex;
    gap: 10px;
}

/* PWA Update Banner */
.pwa-update-banner {
    position: fixed;
    top: 20px;
    left: 20px;
    right: 20px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
    z-index: 1050;
    animation: slideDown 0.3s ease-out;
}

.update-content {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    gap: 15px;
}

/* Toast Notifications */
.toast-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 1060;
    animation: slideInRight 0.3s ease-out;
}

.toast-notification.success {
    background: linear-gradient(135deg, #28a745, #20c997);
}

.toast-notification.warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: #212529;
}

/* Online/Offline Status */
#online-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

#online-status.online {
    background: #d4edda;
    color: #155724;
}

#online-status.offline {
    background: #f8d7da;
    color: #721c24;
}

#offline-indicator {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    background: #dc3545;
    color: white;
    text-align: center;
    padding: 8px;
    font-size: 0.9rem;
    z-index: 1040;
    display: none;
}

/* Offline Mode Styles */
body.offline {
    filter: grayscale(20%);
}

body.offline .btn:not(.btn-secondary):not(.btn-outline-secondary) {
    opacity: 0.7;
    pointer-events: none;
}

/* Animations */
@keyframes slideUp {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideDown {
    from {
        transform: translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .pwa-install-banner {
        left: 10px;
        right: 10px;
        bottom: 10px;
    }

    .install-content {
        padding: 12px 15px;
        gap: 10px;
    }

    .install-actions {
        flex-direction: column;
        gap: 8px;
    }

    .toast-notification {
        right: 10px;
        left: 10px;
    }
}
</style>
`;

// Inject styles into document head
document.head.insertAdjacentHTML('beforeend', pwaStyles);
