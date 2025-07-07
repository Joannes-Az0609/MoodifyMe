-- Voice Messages Schema Update for MoodifyMe
-- Run this to add voice message support to the messaging system

-- Add voice message support columns to messages table
ALTER TABLE messages
ADD COLUMN message_content_type ENUM('text', 'voice', 'file') DEFAULT 'text' AFTER content;

ALTER TABLE messages
ADD COLUMN voice_file_path VARCHAR(500) NULL;

ALTER TABLE messages
ADD COLUMN voice_duration INT NULL COMMENT 'Duration in seconds';

ALTER TABLE messages
ADD COLUMN voice_file_size INT NULL COMMENT 'File size in bytes';

ALTER TABLE messages
ADD COLUMN voice_transcription TEXT NULL COMMENT 'Auto-generated transcription';

-- Create voice_messages table for additional voice message metadata
CREATE TABLE IF NOT EXISTS voice_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL COMMENT 'File size in bytes',
    duration DECIMAL(5,2) NOT NULL COMMENT 'Duration in seconds',
    format ENUM('webm', 'mp3', 'wav', 'ogg') NOT NULL,
    quality ENUM('low', 'medium', 'high') DEFAULT 'medium',
    transcription TEXT NULL,
    transcription_confidence DECIMAL(3,2) NULL COMMENT 'Transcription confidence 0-1',
    waveform_data JSON NULL COMMENT 'Waveform visualization data',
    is_processed BOOLEAN DEFAULT FALSE,
    processing_status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    
    INDEX idx_message (message_id),
    INDEX idx_processing_status (processing_status),
    INDEX idx_created_at (created_at)
);

-- Create voice_message_analytics table for usage tracking
CREATE TABLE IF NOT EXISTS voice_message_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voice_message_id INT NOT NULL,
    user_id INT NOT NULL,
    action ENUM('sent', 'received', 'played', 'downloaded', 'transcribed') NOT NULL,
    device_type ENUM('desktop', 'mobile', 'tablet') NULL,
    browser VARCHAR(100) NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (voice_message_id) REFERENCES voice_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_voice_message (voice_message_id),
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created_at (created_at)
);

-- Add indexes for better performance
ALTER TABLE messages 
ADD INDEX idx_content_type (message_content_type),
ADD INDEX idx_voice_duration (voice_duration);

-- Show updated table structure
DESCRIBE messages;
DESCRIBE voice_messages;
DESCRIBE voice_message_analytics;

-- Sample data for testing (optional)
-- INSERT INTO messages (sender_id, conversation_id, content, message_content_type, voice_file_path, voice_duration) 
-- VALUES (1, 1, 'Voice message', 'voice', 'uploads/voice/2024/01/voice_message_123.webm', 15);
