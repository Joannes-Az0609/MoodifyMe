-- MoodifyMe Social Messaging System Database Schema
-- Run this after the main schema.sql to add social messaging features

USE moodifyme;

-- Chat rooms table (for community chats)
CREATE TABLE IF NOT EXISTS chat_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    room_type ENUM('public', 'private', 'support_group') DEFAULT 'public',
    created_by INT NOT NULL,
    max_participants INT DEFAULT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_room_type (room_type),
    INDEX idx_active (is_active)
);

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
    INDEX idx_type (conversation_type)
);

-- Conversation participants table
CREATE TABLE IF NOT EXISTS conversation_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    user_id INT NOT NULL,
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_participant (conversation_id, user_id),
    INDEX idx_conversation (conversation_id),
    INDEX idx_user (user_id),
    INDEX idx_active (is_active)
);

-- Messages table (for both community and direct messages)
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    message_type ENUM('community', 'direct') NOT NULL,
    chat_room_id INT NULL, -- For community messages
    conversation_id INT NULL, -- For direct messages
    content TEXT NOT NULL,
    message_status ENUM('sent', 'delivered', 'read', 'deleted') DEFAULT 'sent',
    reply_to_message_id INT NULL, -- For threaded conversations
    edited_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (chat_room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (reply_to_message_id) REFERENCES messages(id) ON DELETE SET NULL,
    INDEX idx_sender (sender_id),
    INDEX idx_chat_room (chat_room_id),
    INDEX idx_conversation (conversation_id),
    INDEX idx_created_at (created_at),
    INDEX idx_message_type (message_type),
    INDEX idx_status (message_status)
);

-- Chat room participants table
CREATE TABLE IF NOT EXISTS chat_room_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chat_room_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member', 'moderator', 'admin') DEFAULT 'member',
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    left_at DATETIME NULL,
    is_active BOOLEAN DEFAULT TRUE,
    last_read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chat_room_id) REFERENCES chat_rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_room_participant (chat_room_id, user_id),
    INDEX idx_chat_room (chat_room_id),
    INDEX idx_user (user_id),
    INDEX idx_role (role),
    INDEX idx_active (is_active)
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
    INDEX idx_user (user_id)
);

-- User blocking table
CREATE TABLE IF NOT EXISTS user_blocks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (blocker_id, blocked_id),
    INDEX idx_blocker (blocker_id),
    INDEX idx_blocked (blocked_id)
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

-- Insert default community chat rooms
INSERT INTO chat_rooms (name, description, created_by, room_type) VALUES
('General Support', 'A welcoming space for general emotional support and encouragement', 1, 'public'),
('Daily Check-ins', 'Share how you''re feeling today and connect with others', 1, 'public'),
('Success Stories', 'Celebrate your wins and inspire others with your progress', 1, 'public'),
('Mindfulness & Meditation', 'Discuss mindfulness practices and meditation techniques', 1, 'public');
