-- Remove Voice Message Functionality from MoodifyMe
-- This script removes all voice message related database components

-- Drop voice message related tables
DROP TABLE IF EXISTS voice_message_analytics;
DROP TABLE IF EXISTS voice_messages;

-- Remove voice message columns from messages table
ALTER TABLE messages 
DROP COLUMN IF EXISTS voice_transcription,
DROP COLUMN IF EXISTS voice_file_size,
DROP COLUMN IF EXISTS voice_duration,
DROP COLUMN IF EXISTS voice_file_path;

-- Update message_content_type to only support text
ALTER TABLE messages 
MODIFY COLUMN message_content_type ENUM('text') DEFAULT 'text';

-- Remove voice-related indexes
ALTER TABLE messages 
DROP INDEX IF EXISTS idx_content_type,
DROP INDEX IF EXISTS idx_voice_duration;

-- Clean up any existing voice messages data
UPDATE messages 
SET message_content_type = 'text' 
WHERE message_content_type IN ('voice', 'file');

-- Show updated table structure
DESCRIBE messages;

-- Verify cleanup
SELECT COUNT(*) as remaining_voice_messages 
FROM messages 
WHERE message_content_type != 'text';
