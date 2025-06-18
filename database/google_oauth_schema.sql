-- Google OAuth Schema Updates for MoodifyMe
-- Run this to add Google OAuth support to existing database

USE moodifyme;

-- Add OAuth fields to users table
ALTER TABLE users 
ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER email,
ADD COLUMN oauth_provider VARCHAR(50) NULL AFTER google_id,
ADD COLUMN avatar_url VARCHAR(500) NULL AFTER profile_image,
ADD COLUMN email_verified BOOLEAN DEFAULT FALSE AFTER email,
ADD COLUMN account_type ENUM('regular', 'google', 'facebook', 'twitter') DEFAULT 'regular' AFTER oauth_provider;

-- Make password nullable for OAuth users
ALTER TABLE users 
MODIFY COLUMN password VARCHAR(255) NULL;

-- Create OAuth tokens table for storing refresh tokens
CREATE TABLE IF NOT EXISTS oauth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT NULL,
    token_type VARCHAR(50) DEFAULT 'Bearer',
    expires_at DATETIME NULL,
    scope TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY user_provider (user_id, provider)
);

-- Create social logins table for tracking login attempts
CREATE TABLE IF NOT EXISTS social_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    login_ip VARCHAR(45) NULL,
    user_agent TEXT NULL,
    login_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_provider_user (provider, provider_user_id),
    INDEX idx_login_time (login_at)
);

-- Update existing users to have account_type 'regular'
UPDATE users SET account_type = 'regular' WHERE account_type IS NULL;

-- Add indexes for better performance
CREATE INDEX idx_users_google_id ON users(google_id);
CREATE INDEX idx_users_oauth_provider ON users(oauth_provider);
CREATE INDEX idx_users_account_type ON users(account_type);
CREATE INDEX idx_users_email_verified ON users(email_verified);

-- Show updated table structure
DESCRIBE users;
