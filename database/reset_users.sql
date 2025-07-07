-- MoodifyMe - Reset Users and Remove Administrative Restrictions
-- This SQL script deletes all users and related data

USE modifyMe1;

-- Disable foreign key checks temporarily for easier deletion
SET FOREIGN_KEY_CHECKS = 0;

-- Delete all user-related data (order matters due to relationships)

-- Delete user sessions
DELETE FROM user_sessions;

-- Delete password reset tokens  
DELETE FROM password_reset_tokens;

-- Delete emotions
DELETE FROM emotions;

-- Delete recommendation feedback
DELETE FROM recommendation_feedback;

-- Delete recommendation views (if exists)
DELETE FROM recommendation_views;

-- Delete social features (if they exist)
DELETE FROM user_follows;
DELETE FROM user_connections;
DELETE FROM user_blocks;

-- Delete messaging data
DELETE FROM direct_messages;

-- Delete community data
DELETE FROM community_posts;
DELETE FROM post_reactions;
DELETE FROM post_comments;

-- Delete notifications
DELETE FROM notifications;
DELETE FROM notification_preferences;

-- Delete user online status
DELETE FROM user_online_status;

-- Finally delete all users
DELETE FROM users;

-- Reset auto-increment counters
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE emotions AUTO_INCREMENT = 1;
ALTER TABLE recommendation_feedback AUTO_INCREMENT = 1;
ALTER TABLE community_posts AUTO_INCREMENT = 1;
ALTER TABLE post_reactions AUTO_INCREMENT = 1;
ALTER TABLE post_comments AUTO_INCREMENT = 1;
ALTER TABLE notifications AUTO_INCREMENT = 1;

-- Remove any admin role columns if they exist
-- (These will fail silently if columns don't exist)
ALTER TABLE users DROP COLUMN IF EXISTS role;
ALTER TABLE users DROP COLUMN IF EXISTS is_admin;
ALTER TABLE users DROP COLUMN IF EXISTS admin_level;
ALTER TABLE users DROP COLUMN IF EXISTS permissions;

-- Drop any admin-specific tables if they exist
-- (These will fail silently if tables don't exist)
DROP TABLE IF EXISTS admin_logs;
DROP TABLE IF EXISTS admin_permissions;
DROP TABLE IF EXISTS admin_roles;
DROP TABLE IF EXISTS admin_settings;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Show final user count (should be 0)
SELECT COUNT(*) as remaining_users FROM users;

-- Success message
SELECT 'All users deleted and admin restrictions removed successfully!' as status;
