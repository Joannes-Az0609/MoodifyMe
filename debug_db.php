<?php
/**
 * Database Debug Script for Render
 * Check database connection and environment variables
 */

echo "<h1>MoodifyMe Database Debug</h1>\n";
echo "<pre>\n";

// Check environment
echo "=== ENVIRONMENT DETECTION ===\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'not set') . "\n";
echo "RENDER env var: " . (isset($_ENV['RENDER']) ? 'YES' : 'NO') . "\n";
echo "Is Render detected: " . (isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false ? 'YES' : 'NO') . "\n\n";

// Load config
echo "=== LOADING CONFIG ===\n";
if (isset($_ENV['RENDER']) || strpos($_SERVER['HTTP_HOST'], '.onrender.com') !== false) {
    echo "Loading config.render.php\n";
    require_once 'config.render.php';
} else {
    echo "Loading config.php\n";
    require_once 'config.php';
}

// Check database constants
echo "\n=== DATABASE CONFIGURATION ===\n";
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "\n";
echo "DB_USER: " . (defined('DB_USER') ? DB_USER : 'NOT DEFINED') . "\n";
echo "DB_PASS: " . (defined('DB_PASS') ? (DB_PASS ? '[SET]' : '[EMPTY]') : 'NOT DEFINED') . "\n";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "\n";
echo "DB_PORT: " . (defined('DB_PORT') ? DB_PORT : 'NOT DEFINED') . "\n";
echo "DB_TYPE: " . (defined('DB_TYPE') ? DB_TYPE : 'NOT DEFINED') . "\n";

// Check environment variables
echo "\n=== ENVIRONMENT VARIABLES ===\n";
echo "DATABASE_URL: " . (isset($_ENV['DATABASE_URL']) ? '[SET]' : 'NOT SET') . "\n";
if (isset($_ENV['DATABASE_URL'])) {
    $parsed = parse_url($_ENV['DATABASE_URL']);
    echo "  - Host: " . ($parsed['host'] ?? 'not found') . "\n";
    echo "  - User: " . ($parsed['user'] ?? 'not found') . "\n";
    echo "  - Pass: " . (isset($parsed['pass']) ? '[SET]' : 'not found') . "\n";
    echo "  - DB: " . (isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'not found') . "\n";
    echo "  - Port: " . ($parsed['port'] ?? 'not found') . "\n";
}

// Test database connection
echo "\n=== DATABASE CONNECTION TEST ===\n";
try {
    require_once 'includes/db_connect.php';
    $conn = getDbConnection();
    
    if ($conn) {
        echo "✅ Connection successful!\n";
        echo "Connection type: " . get_class($conn) . "\n";
        
        // Test query
        if (defined('DB_TYPE') && DB_TYPE === 'pgsql') {
            $stmt = $conn->prepare("SELECT version() as version");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "PostgreSQL Version: " . $result['version'] . "\n";
        } else {
            $result = $conn->query("SELECT VERSION() as version");
            $row = $result->fetch_assoc();
            echo "MySQL Version: " . $row['version'] . "\n";
        }
    } else {
        echo "❌ Connection failed - no connection object returned\n";
    }
} catch (Exception $e) {
    echo "❌ Connection failed with error:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== RENDER SPECIFIC CHECKS ===\n";
echo "All environment variables starting with 'DATABASE':\n";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'DATABASE') === 0) {
        echo "$key: " . (strlen($value) > 50 ? '[LONG VALUE]' : $value) . "\n";
    }
}

echo "</pre>\n";
?>
