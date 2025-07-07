/**
 * MoodifyMe - Global Message Notification System
 * Handles real-time message notifications across all pages
 */

class MessageNotificationSystem {
    constructor() {
        this.lastMessageCheck = Math.floor(Date.now() / 1000);
        this.notificationInterval = null;
        this.isInitialized = false;
        this.currentPage = window.location.pathname;
        
        // Initialize if user is logged in
        if (document.getElementById('notification-count')) {
            this.init();
        }
    }
    
    init() {
        if (this.isInitialized) return;
        
        this.requestNotificationPermission();
        this.startNotificationChecking();
        this.isInitialized = true;
        
        console.log('Message notification system initialized');
    }
    
    requestNotificationPermission() {
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission().then(permission => {
                console.log('Notification permission:', permission);
            });
        }
    }
    
    startNotificationChecking() {
        // Check for new messages every 10 seconds (less frequent on non-message pages)
        const interval = this.currentPage.includes('messages.php') ? 5000 : 10000;
        
        this.notificationInterval = setInterval(() => {
            this.checkForNewMessages();
        }, interval);
        
        // Initial check
        this.checkForNewMessages();
    }
    
    checkForNewMessages() {
        fetch(`${window.APP_URL || ''}/api/check_new_messages.php?last_check=${this.lastMessageCheck}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.new_messages && data.new_messages.length > 0) {
                    data.new_messages.forEach(message => {
                        this.showMessageNotification(message);
                    });
                    this.lastMessageCheck = data.timestamp;
                    
                    // Update notification bell count
                    this.updateNotificationBellCount();
                }
            })
            .catch(error => {
                console.log('Could not check for new messages:', error);
            });
    }
    
    showMessageNotification(message) {
        const senderName = message.sender.display_name || message.sender.username;
        const title = `New message from ${senderName}`;
        const body = message.preview;
        
        // Don't show notification if we're already on the conversation page
        const currentConversationId = this.getCurrentConversationId();
        if (currentConversationId && currentConversationId == message.conversation_id) {
            return;
        }
        
        // Show browser notification if permission granted
        if ('Notification' in window && Notification.permission === 'granted') {
            const notification = new Notification(title, {
                body: body,
                icon: message.sender.profile_image ? 
                      `${window.APP_URL || ''}/${message.sender.profile_image}` : 
                      `${window.APP_URL || ''}/assets/images/default-avatar.png`,
                tag: `message-${message.conversation_id}`,
                requireInteraction: false
            });
            
            // Auto close after 6 seconds
            setTimeout(() => notification.close(), 6000);
            
            // Handle click to open conversation
            notification.onclick = function() {
                window.focus();
                window.location.href = `${window.APP_URL || ''}/pages/messages.php?conversation=${message.conversation_id}`;
                notification.close();
            };
        }
        
        // Show toast notification
        if (typeof window.showNotificationToast === 'function') {
            window.showNotificationToast(title, body, 'message');
        }
        
        // Play notification sound
        this.playNotificationSound();
    }
    
    getCurrentConversationId() {
        // Try to get conversation ID from URL or global variable
        const urlParams = new URLSearchParams(window.location.search);
        const urlConversationId = urlParams.get('conversation');
        
        if (urlConversationId) return urlConversationId;
        
        // Check for global variable (set in messages.php)
        if (typeof window.currentConversationId !== 'undefined') {
            return window.currentConversationId;
        }
        
        return null;
    }
    
    playNotificationSound() {
        try {
            // Create a short notification beep
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
            oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);
            
            gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        } catch (e) {
            // Fallback to simple audio file
            try {
                const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLZiTYIG2m98OScTgwOUarm7blmGgU7k9n1unEiBC13yO/eizEIHWq+8+OWT');
                audio.volume = 0.2;
                audio.play().catch(() => {
                    // Ignore audio play errors
                });
            } catch (e2) {
                // Ignore audio errors
            }
        }
    }
    
    updateNotificationBellCount() {
        if (typeof window.updateNotificationBellCount === 'function') {
            window.updateNotificationBellCount();
        }
    }
    
    destroy() {
        if (this.notificationInterval) {
            clearInterval(this.notificationInterval);
            this.notificationInterval = null;
        }
        this.isInitialized = false;
    }
}

// Initialize the notification system when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    window.messageNotificationSystem = new MessageNotificationSystem();
});

// Cleanup when leaving page
window.addEventListener('beforeunload', function() {
    if (window.messageNotificationSystem) {
        window.messageNotificationSystem.destroy();
    }
});

// Export for use in other scripts
window.MessageNotificationSystem = MessageNotificationSystem;
