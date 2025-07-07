-- MoodifyMe Notifications System Schema
-- Run this to add comprehensive notification functionality

USE modifyMe1;

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('friend_request', 'friend_accepted', 'friend_declined', 'message', 'system', 'mood_milestone', 'recommendation') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL, -- Store additional data like user_ids, request_ids, etc.
    is_read BOOLEAN DEFAULT FALSE,
    action_url VARCHAR(500) NULL, -- URL to navigate when notification is clicked
    action_text VARCHAR(100) NULL, -- Text for action button (e.g., "Accept", "View", "Reply")
    related_user_id INT NULL, -- ID of user who triggered the notification
    related_item_id INT NULL, -- ID of related item (message_id, request_id, etc.)
    expires_at DATETIME NULL, -- Optional expiration for time-sensitive notifications
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_user_unread (user_id, is_read),
    INDEX idx_expires_at (expires_at)
);

-- Create notification preferences table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    friend_requests BOOLEAN DEFAULT TRUE,
    friend_accepted BOOLEAN DEFAULT TRUE,
    messages BOOLEAN DEFAULT TRUE,
    system_updates BOOLEAN DEFAULT TRUE,
    mood_milestones BOOLEAN DEFAULT TRUE,
    recommendations BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT FALSE,
    push_notifications BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_preferences (user_id)
);

-- Insert default notification preferences for existing users
INSERT IGNORE INTO notification_preferences (user_id)
SELECT id FROM users;

-- Show table structures
DESCRIBE notifications;
DESCRIBE notification_preferences;

-- Sample data for testing (optional)
-- INSERT INTO notifications (user_id, type, title, message, related_user_id, action_url, action_text) VALUES
-- (1, 'friend_request', 'New Friend Request', 'John Doe wants to connect with you', 2, '/pages/connections.php?tab=requests', 'View Request'),
-- (1, 'system', 'Welcome to MoodifyMe!', 'Thank you for joining our community. Start by checking your mood!', NULL, '/pages/dashboard.php', 'Get Started');
