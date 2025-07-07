<?php
/**
 * MoodifyMe - Reset Users and Remove Administrative Restrictions
 * This script deletes all users and reverts any admin restrictions
 */

// Include configuration
require_once '../config.php';
require_once '../includes/db_connect.php';

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>MoodifyMe User Reset and Admin Restrictions Removal</h2>\n";

// Check if we're connected to the database
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

echo "<p>✓ Database connection successful</p>\n";

// Start transaction for safety
$conn->begin_transaction();

try {
    // 1. Get current user count
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    $userCount = $row['count'];
    
    echo "<p>Current users in database: {$userCount}</p>\n";
    
    if ($userCount > 0) {
        // 2. Delete all related data first (due to foreign key constraints)
        
        // Delete user sessions
        $result = $conn->query("DELETE FROM user_sessions");
        echo "<p>✓ Deleted user sessions</p>\n";
        
        // Delete password reset tokens
        $result = $conn->query("DELETE FROM password_reset_tokens");
        echo "<p>✓ Deleted password reset tokens</p>\n";
        
        // Delete emotions
        $result = $conn->query("DELETE FROM emotions");
        echo "<p>✓ Deleted emotion records</p>\n";
        
        // Delete recommendation feedback
        $result = $conn->query("DELETE FROM recommendation_feedback");
        echo "<p>✓ Deleted recommendation feedback</p>\n";
        
        // Delete recommendation views
        $result = $conn->query("DELETE FROM recommendation_views");
        echo "<p>✓ Deleted recommendation views</p>\n";
        
        // Delete user follows (if exists)
        $conn->query("DELETE FROM user_follows");
        echo "<p>✓ Deleted user follows</p>\n";
        
        // Delete user connections (if exists)
        $conn->query("DELETE FROM user_connections");
        echo "<p>✓ Deleted user connections</p>\n";
        
        // Delete user blocks (if exists)
        $conn->query("DELETE FROM user_blocks");
        echo "<p>✓ Deleted user blocks</p>\n";
        
        // Delete direct messages (if exists)
        $conn->query("DELETE FROM direct_messages");
        echo "<p>✓ Deleted direct messages</p>\n";
        
        // Delete community posts
        $conn->query("DELETE FROM community_posts");
        echo "<p>✓ Deleted community posts</p>\n";
        
        // Delete post reactions
        $conn->query("DELETE FROM post_reactions");
        echo "<p>✓ Deleted post reactions</p>\n";
        
        // Delete post comments
        $conn->query("DELETE FROM post_comments");
        echo "<p>✓ Deleted post comments</p>\n";
        
        // Delete notifications
        $conn->query("DELETE FROM notifications");
        echo "<p>✓ Deleted notifications</p>\n";
        
        // Delete notification preferences
        $conn->query("DELETE FROM notification_preferences");
        echo "<p>✓ Deleted notification preferences</p>\n";
        
        // Delete user online status
        $conn->query("DELETE FROM user_online_status");
        echo "<p>✓ Deleted user online status</p>\n";
        
        // 3. Finally delete all users
        $result = $conn->query("DELETE FROM users");
        echo "<p>✓ Deleted all users</p>\n";
        
        // 4. Reset auto-increment counters
        $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE emotions AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE recommendation_feedback AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE recommendation_views AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE community_posts AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE post_reactions AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE post_comments AUTO_INCREMENT = 1");
        $conn->query("ALTER TABLE notifications AUTO_INCREMENT = 1");
        echo "<p>✓ Reset auto-increment counters</p>\n";
    } else {
        echo "<p>No users found to delete</p>\n";
    }
    
    // 5. Remove any administrative restrictions
    echo "<h3>Removing Administrative Restrictions:</h3>\n";
    
    // Check if there are any admin-only restrictions in the code
    // Most restrictions are session-based, so clearing users removes them
    
    // Remove any admin role columns if they exist
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE users DROP COLUMN role");
        echo "<p>✓ Removed user role column</p>\n";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'is_admin'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE users DROP COLUMN is_admin");
        echo "<p>✓ Removed is_admin column</p>\n";
    }
    
    $result = $conn->query("SHOW COLUMNS FROM users LIKE 'admin_level'");
    if ($result->num_rows > 0) {
        $conn->query("ALTER TABLE users DROP COLUMN admin_level");
        echo "<p>✓ Removed admin_level column</p>\n";
    }
    
    // Check for any admin-specific tables
    $result = $conn->query("SHOW TABLES LIKE 'admin_%'");
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tableName = $row[array_keys($row)[0]];
            $conn->query("DROP TABLE $tableName");
            echo "<p>✓ Removed admin table: $tableName</p>\n";
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "<h3>✅ Reset Complete!</h3>\n";
    echo "<p><strong>Summary:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✓ All users deleted from database</li>\n";
    echo "<li>✓ All user-related data removed</li>\n";
    echo "<li>✓ Auto-increment counters reset</li>\n";
    echo "<li>✓ Administrative restrictions removed</li>\n";
    echo "<li>✓ Database is now clean and ready for fresh users</li>\n";
    echo "</ul>\n";
    
    echo "<h3>Next Steps:</h3>\n";
    echo "<ol>\n";
    echo "<li><strong>Create new admin user:</strong> Register through the normal signup process</li>\n";
    echo "<li><strong>Test functionality:</strong> All features should work without restrictions</li>\n";
    echo "<li><strong>No admin privileges:</strong> All users will have equal access</li>\n";
    echo "</ol>\n";
    
    echo "<h3>Files That May Reference Admin Restrictions:</h3>\n";
    echo "<ul>\n";
    echo "<li><code>pages/admin_contact_messages.php</code> - Only requires login, no admin role</li>\n";
    echo "<li><code>includes/auth_check.php</code> - Basic authentication only</li>\n";
    echo "<li>All other files use session-based authentication without admin roles</li>\n";
    echo "</ul>\n";
    
    echo "<p><em>Note: The admin contact messages page will still work for any logged-in user. If you want to restrict it further, you can add role-based checks later.</em></p>\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    echo "<p style='color: red;'>❌ Error occurred: " . $e->getMessage() . "</p>\n";
    echo "<p style='color: red;'>Transaction rolled back. No changes made.</p>\n";
}

// Close connection
$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    line-height: 1.6;
}

h2 {
    color: #333;
    border-bottom: 2px solid #007bff;
    padding-bottom: 10px;
}

h3 {
    color: #555;
    margin-top: 30px;
}

p {
    margin: 10px 0;
}

ul, ol {
    margin: 15px 0;
    padding-left: 30px;
}

li {
    margin: 5px 0;
}

code {
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 3px;
    font-family: 'Courier New', monospace;
}

.success {
    color: #28a745;
}

.error {
    color: #dc3545;
}
</style>
