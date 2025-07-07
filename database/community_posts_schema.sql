-- MoodifyMe Community Posts System
-- Replace chat rooms with individual posts/blocks

USE modifyMe1;

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

-- Insert some sample posts to get started
INSERT INTO community_posts (user_id, title, content, post_type, mood_tag) VALUES
(1, 'Welcome to our Community!', 'This is a safe space to share your thoughts, feelings, and experiences. Feel free to post about your mood, ask for support, or celebrate your wins!', 'general', 'welcoming'),
(1, 'Daily Check-in: How are you feeling today?', 'Share how you''re feeling today and what''s on your mind. Remember, every feeling is valid and you''re not alone in your journey.', 'mood_share', 'supportive'),
(1, 'Tips for Managing Anxiety', 'Here are some techniques that have helped me when feeling anxious:\n\n1. Deep breathing exercises\n2. Grounding techniques (5-4-3-2-1 method)\n3. Progressive muscle relaxation\n4. Mindful walking\n\nWhat techniques work for you?', 'support', 'helpful');

-- Show table structures
DESCRIBE community_posts;
DESCRIBE post_reactions;
DESCRIBE post_comments;
DESCRIBE post_reports;
