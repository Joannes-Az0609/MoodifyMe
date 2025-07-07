-- Remove Chat Room Functionality from MoodifyMe Database
-- This script removes all chat room related tables and data while preserving direct messaging

USE modifyMe1;

-- First, remove any foreign key constraints that might prevent table deletion
SET FOREIGN_KEY_CHECKS = 0;

-- Drop chat room related tables
DROP TABLE IF EXISTS chat_room_participants;
DROP TABLE IF EXISTS chat_rooms;

-- Remove chat_room_id column from messages table if it exists
-- Check if the column exists first
SET @sql = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = 'modifyMe1' 
     AND TABLE_NAME = 'messages' 
     AND COLUMN_NAME = 'chat_room_id') > 0,
    'ALTER TABLE messages DROP COLUMN chat_room_id',
    'SELECT "chat_room_id column does not exist" as message'
));

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update message_type ENUM to remove 'community' type if it exists
-- This will keep only 'direct' message type
ALTER TABLE messages MODIFY COLUMN message_type ENUM('direct') DEFAULT 'direct';

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Clean up any orphaned messages that might have been community messages
DELETE FROM messages WHERE message_type NOT IN ('direct');

SELECT 'Chat room functionality successfully removed from database' as status;
