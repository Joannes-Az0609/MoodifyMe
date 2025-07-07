-- MoodifyMe Complete Database Schema
-- Database name: modifyMe1
-- This file contains all tables needed for the MoodifyMe project

-- Create database
CREATE DATABASE IF NOT EXISTS modifyMe1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use database
USE modifyMe1;

-- =====================================================
-- CORE TABLES
-- =====================================================

-- Users table (enhanced with OAuth and social features)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    email_verified BOOLEAN DEFAULT FALSE,
    google_id VARCHAR(255) NULL UNIQUE,
    oauth_provider VARCHAR(50) NULL,
    password_hash VARCHAR(255) NULL, -- Nullable for OAuth users
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    display_name VARCHAR(100) NULL,
    profile_picture VARCHAR(255),
    avatar_url VARCHAR(500) NULL, -- For OAuth profile pictures
    bio TEXT,
    date_of_birth DATE,
    gender ENUM('male', 'female', 'other', 'prefer_not_to_say'),
    location VARCHAR(100),
    account_type ENUM('regular', 'google', 'facebook', 'twitter') DEFAULT 'regular',
    is_verified BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_public BOOLEAN DEFAULT TRUE,
    privacy_level ENUM('public', 'friends', 'private') DEFAULT 'public',
    allow_direct_messages ENUM('everyone', 'connections', 'none') DEFAULT 'connections',
    show_online_status BOOLEAN DEFAULT TRUE,
    follower_count INT DEFAULT 0,
    following_count INT DEFAULT 0,
    connection_count INT DEFAULT 0,
    preferences JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login DATETIME,
    last_active DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_google_id (google_id),
    INDEX idx_oauth_provider (oauth_provider),
    INDEX idx_account_type (account_type),
    INDEX idx_email_verified (email_verified),
    INDEX idx_is_active (is_active),
    INDEX idx_is_public (is_public)
);

-- Emotions table
CREATE TABLE IF NOT EXISTS emotions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    emotion_type VARCHAR(50) NOT NULL,
    confidence FLOAT NOT NULL,
    source VARCHAR(20) NOT NULL, -- 'text', 'voice', 'face'
    raw_input TEXT,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_emotion_type (emotion_type),
    INDEX idx_source (source),
    INDEX idx_created_at (created_at)
);

-- Recommendations table
CREATE TABLE IF NOT EXISTS recommendations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    type VARCHAR(50) NOT NULL, -- 'music', 'movies', 'african_meals'
    source_emotion VARCHAR(50) NOT NULL,
    target_emotion VARCHAR(50) NOT NULL,
    content TEXT,
    image_url VARCHAR(255),
    link VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_type (type),
    INDEX idx_source_target (source_emotion, target_emotion)
);

-- Recommendation logs table
CREATE TABLE IF NOT EXISTS recommendation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    emotion_id INT NOT NULL,
    recommendation_id INT NOT NULL,
    viewed_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (emotion_id) REFERENCES emotions(id) ON DELETE CASCADE,
    FOREIGN KEY (recommendation_id) REFERENCES recommendations(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_emotion_id (emotion_id),
    INDEX idx_recommendation_id (recommendation_id)
);

-- Recommendation feedback table
CREATE TABLE IF NOT EXISTS recommendation_feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    recommendation_id INT NOT NULL,
    feedback_type ENUM('like', 'dislike') NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recommendation_id) REFERENCES recommendations(id) ON DELETE CASCADE,
    UNIQUE KEY user_recommendation (user_id, recommendation_id),
    
    INDEX idx_user_id (user_id),
    INDEX idx_recommendation_id (recommendation_id)
);

-- =====================================================
-- AUTHENTICATION & SECURITY TABLES
-- =====================================================

-- User sessions table
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_session_token (session_token),
    INDEX idx_expires_at (expires_at)
);

-- Password reset tokens table
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_token (token),
    INDEX idx_expires_at (expires_at)
);

-- OAuth tokens table for storing refresh tokens
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
    UNIQUE KEY user_provider (user_id, provider),
    
    INDEX idx_user_id (user_id),
    INDEX idx_provider (provider)
);

-- Social logins table for tracking login attempts
CREATE TABLE IF NOT EXISTS social_logins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    provider VARCHAR(50) NOT NULL,
    provider_user_id VARCHAR(255) NOT NULL,
    login_ip VARCHAR(45) NULL,
    user_agent TEXT NULL,
    login_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_provider_user (provider, provider_user_id),
    INDEX idx_login_time (login_at)
);

-- User preferences table
CREATE TABLE IF NOT EXISTS user_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    preference_key VARCHAR(50) NOT NULL,
    preference_value TEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY user_preference (user_id, preference_key),

    INDEX idx_user_id (user_id),
    INDEX idx_preference_key (preference_key)
);

-- =====================================================
-- SOCIAL FEATURES TABLES
-- =====================================================

-- User follows table (who follows whom)
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

-- User connections table (for direct messaging permissions)
CREATE TABLE IF NOT EXISTS user_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requester_id INT NOT NULL,
    receiver_id INT NOT NULL,
    status ENUM('pending', 'accepted', 'blocked', 'declined') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_connection (requester_id, receiver_id),

    INDEX idx_requester (requester_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_status (status)
);

-- User blocks table (for blocking users)
CREATE TABLE IF NOT EXISTS user_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    reason VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (blocker_id, blocked_id),

    INDEX idx_blocker (blocker_id),
    INDEX idx_blocked (blocked_id)
);

-- User online status table
CREATE TABLE IF NOT EXISTS user_online_status (
    user_id INT PRIMARY KEY,
    status ENUM('online', 'away', 'busy', 'offline') DEFAULT 'offline',
    last_seen DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_status (status),
    INDEX idx_last_seen (last_seen)
);

-- =====================================================
-- MESSAGING SYSTEM TABLES
-- =====================================================

-- Chat rooms functionality removed

-- Chat room participants table removed

-- Conversations table (for direct messages)
CREATE TABLE IF NOT EXISTS conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_type ENUM('direct', 'group') DEFAULT 'direct',
    created_by INT NOT NULL,
    title VARCHAR(255) NULL, -- For group conversations
    last_message_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_last_message (last_message_at),
    INDEX idx_type (conversation_type),
    INDEX idx_created_by (created_by)
);

-- Conversation participants table
CREATE TABLE IF NOT EXISTS conversation_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (conversation_id, user_id),

    INDEX idx_conversation (conversation_id),
    INDEX idx_user (user_id)
);

-- Messages table (for direct messages only)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    message_type ENUM('direct') NOT NULL DEFAULT 'direct',
    conversation_id INT NOT NULL, -- For direct messages
    content TEXT NOT NULL,
    message_status ENUM('sent', 'delivered', 'read', 'deleted') DEFAULT 'sent',
    reply_to_message_id INT NULL, -- For threaded conversations
    edited_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to_message_id) REFERENCES messages(id) ON DELETE SET NULL,

    INDEX idx_sender (sender_id),
    INDEX idx_message_type (message_type),
    INDEX idx_conversation (conversation_id),
    INDEX idx_created_at (created_at),
    INDEX idx_reply_to (reply_to_message_id)
);

-- Message reactions table (likes, hearts, etc.)
CREATE TABLE IF NOT EXISTS message_reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type ENUM('like', 'heart', 'support', 'hug') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reaction (message_id, user_id, reaction_type),

    INDEX idx_message (message_id),
    INDEX idx_user (user_id),
    INDEX idx_reaction_type (reaction_type)
);

-- Message reports table (for moderation)
CREATE TABLE IF NOT EXISTS message_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    reporter_id INT NOT NULL,
    reason ENUM('spam', 'harassment', 'inappropriate', 'other') NOT NULL,
    description TEXT NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    reviewed_by INT NULL,
    reviewed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_message (message_id),
    INDEX idx_reporter (reporter_id),
    INDEX idx_status (status)
);

-- =====================================================
-- COMMUNITY POSTS SYSTEM TABLES
-- =====================================================

-- Community posts table (replaces chat rooms concept)
CREATE TABLE IF NOT EXISTS community_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    post_type ENUM('general', 'support', 'success', 'question', 'mood_share') DEFAULT 'general',
    mood_tag VARCHAR(50) NULL, -- Optional mood associated with the post
    is_anonymous BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

    INDEX idx_user_id (user_id),
    INDEX idx_post_type (post_type),
    INDEX idx_created_at (created_at),
    INDEX idx_active (is_active),
    INDEX idx_mood_tag (mood_tag)
);

-- Post reactions table (likes, hearts, support, etc.)
CREATE TABLE IF NOT EXISTS post_reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type ENUM('like', 'heart', 'support', 'hug', 'celebrate') NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post_reaction (post_id, user_id, reaction_type),

    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reaction_type (reaction_type)
);

-- Post comments table (responses to posts)
CREATE TABLE IF NOT EXISTS post_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    reply_to_comment_id INT NULL, -- For nested replies
    is_anonymous BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to_comment_id) REFERENCES post_comments(id) ON DELETE CASCADE,

    INDEX idx_post_id (post_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reply_to (reply_to_comment_id),
    INDEX idx_created_at (created_at),
    INDEX idx_active (is_active)
);

-- Post reports table (for moderation)
CREATE TABLE IF NOT EXISTS post_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NULL,
    comment_id INT NULL,
    reported_by INT NOT NULL,
    report_reason ENUM('spam', 'inappropriate', 'harassment', 'misinformation', 'other') NOT NULL,
    report_details TEXT NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at DATETIME NULL,
    reviewed_by INT NULL,
    FOREIGN KEY (post_id) REFERENCES community_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (comment_id) REFERENCES post_comments(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_post_id (post_id),
    INDEX idx_comment_id (comment_id),
    INDEX idx_reported_by (reported_by),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);

-- =====================================================
-- NOTIFICATIONS SYSTEM TABLES
-- =====================================================

-- Notifications table
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
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,

    INDEX idx_user_id (user_id),
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at),
    INDEX idx_related_user (related_user_id),
    INDEX idx_expires_at (expires_at)
);

-- Notification preferences table
CREATE TABLE IF NOT EXISTS notification_preferences (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    email_notifications BOOLEAN DEFAULT TRUE,
    push_notifications BOOLEAN DEFAULT TRUE,
    friend_requests BOOLEAN DEFAULT TRUE,
    messages BOOLEAN DEFAULT TRUE,
    mood_reminders BOOLEAN DEFAULT TRUE,
    system_updates BOOLEAN DEFAULT TRUE,
    marketing BOOLEAN DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_prefs (user_id),

    INDEX idx_user_id (user_id)
);

-- =====================================================
-- CONTACT & SUPPORT TABLES
-- =====================================================

-- Contact messages table
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_email (email)
);

-- =====================================================
-- SAMPLE DATA INSERTS
-- =====================================================

-- Chat room sample data removed

-- Insert some sample community posts
INSERT INTO community_posts (user_id, title, content, post_type, mood_tag) VALUES
(1, 'Welcome to our Community!', 'This is a safe space to share your thoughts, feelings, and experiences. Feel free to post about your mood, ask for support, or celebrate your wins!', 'general', 'welcoming'),
(1, 'Daily Check-in: How are you feeling today?', 'Share how you''re feeling today and what''s on your mind. Remember, every feeling is valid and you''re not alone in your journey.', 'mood_share', 'supportive'),
(1, 'Tips for Managing Anxiety', 'Here are some techniques that have helped me when feeling anxious:\n\n1. Deep breathing exercises\n2. Grounding techniques (5-4-3-2-1 method)\n3. Progressive muscle relaxation\n4. Mindful walking\n\nWhat techniques work for you?', 'support', 'helpful');

-- Insert default notification preferences for system user
INSERT INTO notification_preferences (user_id) VALUES (1);

-- =====================================================
-- FINAL NOTES
-- =====================================================

-- This schema includes:
-- 1. Core user management with OAuth support
-- 2. Emotion tracking and recommendations
-- 3. Social features (follows, connections, blocks)
-- 4. Messaging system (community and direct)
-- 5. Community posts with reactions and comments
-- 6. Comprehensive notification system
-- 7. Contact form support
-- 8. Proper indexing for performance
-- 9. Foreign key constraints for data integrity
-- 10. Sample data for initial setup

-- To use this schema:
-- 1. Update your config.php to use 'modifyMe1' as DB_NAME
-- 2. Run this SQL file to create the complete database
-- 3. Update any hardcoded database references in your code
