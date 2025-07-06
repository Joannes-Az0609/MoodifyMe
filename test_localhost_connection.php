<?php
/**
 * Test localhost database connection
 */

echo "Testing localhost database connection...\n";

require_once 'includes/db_connect.php';

try {
    $result = $conn->query('SELECT DATABASE() as db, VERSION() as version');
    $row = $result->fetch_assoc();
    
    echo "✅ SUCCESS: Connected to MySQL!\n";
    echo "Database: " . ($row['db'] ?: 'No database selected') . "\n";
    echo "MySQL Version: " . $row['version'] . "\n";
    echo "Host: " . DB_HOST . "\n";
    echo "User: " . DB_USER . "\n";
    
    // Test if moodifyme database exists
    $result = $conn->query("SHOW DATABASES LIKE 'moodifyme'");
    if ($result->num_rows > 0) {
        echo "✅ MoodifyMe database exists\n";
        
        // Check if we're connected to it
        $conn->select_db('moodifyme');
        $result = $conn->query("SHOW TABLES");
        echo "Tables in moodifyme database: " . $result->num_rows . "\n";
    } else {
        echo "⚠️  MoodifyMe database does not exist - you may need to run migration\n";
    }
    
} catch(Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\nConfiguration being used:\n";
echo "Environment detected: " . ($_SERVER['HTTP_HOST'] ?? 'CLI') . "\n";
if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || 
    $_SERVER['HTTP_HOST'] === '127.0.0.1' || 
    !isset($_SERVER['HTTP_HOST'])) {
    echo "Using: config.php (localhost)\n";
} else {
    echo "Using: production configuration\n";
}
?>
