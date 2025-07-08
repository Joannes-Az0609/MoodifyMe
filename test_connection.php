<?php
/**
 * Test Database Connection for MoodifyMe
 * This script tests the database connection using PDO
 */

// Include the database connection
require_once 'includes/db_connect.php';

echo "<h1>MoodifyMe Database Connection Test</h1>\n";
echo "<pre>\n";

try {
    $conn = getDbConnection();
    
    if ($conn) {
        echo "✅ Database connection successful!\n";
        echo "Connection type: " . get_class($conn) . "\n";
        
        // Test a simple query
        if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
            // PostgreSQL test
            $stmt = $conn->prepare("SELECT version() as version, current_database() as database");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "PostgreSQL Version: " . $result['version'] . "\n";
            echo "Current Database: " . $result['database'] . "\n";
        } else {
            // MySQL test
            $stmt = $conn->prepare("SELECT VERSION() as version, DATABASE() as database");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "MySQL Version: " . $result['version'] . "\n";
            echo "Current Database: " . $result['database'] . "\n";
        }
        
        // Test if users table exists
        try {
            if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = 'users'");
            } else {
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_name = 'users' AND table_schema = DATABASE()");
            }
            $stmt->execute();
            $result = $stmt->fetch();
            
            if ($result['count'] > 0) {
                echo "✅ Users table exists\n";
                
                // Count users
                $stmt = $conn->prepare("SELECT COUNT(*) as user_count FROM users");
                $stmt->execute();
                $userResult = $stmt->fetch();
                echo "Total users: " . $userResult['user_count'] . "\n";
            } else {
                echo "⚠️  Users table does not exist\n";
            }
        } catch (Exception $e) {
            echo "⚠️  Could not check users table: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "❌ Failed to get database connection\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Error details: " . $e->getFile() . " on line " . $e->getLine() . "\n";
}

echo "\n=== Configuration Details ===\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'not defined') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'not defined') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'not defined') . "\n";
echo "DB_TYPE: " . (defined('DB_TYPE') ? DB_TYPE : 'not defined') . "\n";
echo "DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'not defined') . "\n";

echo "</pre>\n";
?>
