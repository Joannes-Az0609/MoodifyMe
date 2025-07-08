-- MoodifyMe - Fix Social Features Database Migration
-- Run this script to fix the "Failed to accept connection request" error
-- This adds missing columns and tables required for social features

USE modifyMe1;

-- Add missing columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS follower_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS following_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS connection_count INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS display_name VARCHAR(100) NULL,
ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS is_public BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS privacy_level ENUM('public', 'friends', 'private') DEFAULT 'public',
ADD COLUMN IF NOT EXISTS allow_direct_messages ENUM('everyone', 'connections', 'none') DEFAULT 'connections',
ADD COLUMN IF NOT EXISTS show_online_status BOOLEAN DEFAULT TRUE,
ADD COLUMN IF NOT EXISTS last_active DATETIME DEFAULT CURRENT_TIMESTAMP;

-- Create notifications table if it doesn't exist
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('friend_request', 'friend_accepted', 'friend_declined', 'message', 'system', 'mood_milestone', 'recommendation') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    data JSON NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Create user_follows table if it doesn't exist
CREATE TABLE IF NOT EXISTS user_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    follower_id INT NOT NULL,
    following_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (follower_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (following_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_follow (follower_id, following_id),
    INDEX idx_follower (follower_id),
    INDEX idx_following (following_id)
);

-- Create user_online_status table if it doesn't exist
CREATE TABLE IF NOT EXISTS user_online_status (
    user_id INT PRIMARY KEY,
    status ENUM('online', 'away', 'busy', 'offline') DEFAULT 'offline',
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_last_seen (last_seen)
);

-- Update existing connection counts for users who already have connections
UPDATE users SET connection_count = (
    SELECT COUNT(*)
    FROM user_connections
    WHERE (requester_id = users.id OR receiver_id = users.id)
    AND status = 'accepted'
) WHERE connection_count = 0;

-- Update existing follower counts for users who already have followers
UPDATE users SET follower_count = (
    SELECT COUNT(*)
    FROM user_follows
    WHERE following_id = users.id
) WHERE follower_count = 0;

-- Update existing following counts for users who are already following others
UPDATE users SET following_count = (
    SELECT COUNT(*)
    FROM user_follows
    WHERE follower_id = users.id
) WHERE following_count = 0;

-- Show success message
SELECT 'Social features database migration completed successfully!' as Status;

-- Show table structures to verify
DESCRIBE users;
DESCRIBE notifications;
DESCRIBE user_online_status;
