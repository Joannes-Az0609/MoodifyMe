-- MoodifyMe User Table Updates for Social Features
-- Run this to add social features to existing users table

USE moodifyme;

-- Add social-related columns to users table
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS display_name VARCHAR(100) NULL AFTER username,
ADD COLUMN IF NOT EXISTS is_public BOOLEAN DEFAULT TRUE AFTER bio,
ADD COLUMN IF NOT EXISTS allow_direct_messages ENUM('everyone', 'connections', 'none') DEFAULT 'connections' AFTER is_public,
ADD COLUMN IF NOT EXISTS show_online_status BOOLEAN DEFAULT TRUE AFTER allow_direct_messages,
ADD COLUMN IF NOT EXISTS follower_count INT DEFAULT 0 AFTER show_online_status,
ADD COLUMN IF NOT EXISTS following_count INT DEFAULT 0 AFTER follower_count,
ADD COLUMN IF NOT EXISTS connection_count INT DEFAULT 0 AFTER following_count,
ADD COLUMN IF NOT EXISTS last_active DATETIME DEFAULT CURRENT_TIMESTAMP AFTER last_login;

-- Add indexes for better performance
ALTER TABLE users 
ADD INDEX IF NOT EXISTS idx_is_public (is_public),
ADD INDEX IF NOT EXISTS idx_last_active (last_active),
ADD INDEX IF NOT EXISTS idx_display_name (display_name);

-- Update existing users to have default social settings
UPDATE users SET 
    display_name = username,
    is_public = TRUE,
    allow_direct_messages = 'connections',
    show_online_status = TRUE,
    last_active = COALESCE(last_login, created_at)
WHERE display_name IS NULL;
