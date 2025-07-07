<?php
/**
 * MoodifyMe - Import Sample Community Posts
 * Run this script to populate your database with sample community posts
 */

// Include configuration
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

echo "🚀 Starting Community Posts Import...\n\n";

try {
    // Read the SQL file
    $sqlFile = __DIR__ . '/sample_community_posts.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if ($sql === false) {
        throw new Exception("Failed to read SQL file");
    }
    
    echo "📁 SQL file loaded successfully\n";
    
    // Split the SQL into individual statements
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            return !empty($stmt) &&
                   substr($stmt, 0, 2) !== '--' &&
                   substr($stmt, 0, 2) !== '/*' &&
                   substr($stmt, 0, 3) !== 'USE';
        }
    );
    
    echo "📊 Found " . count($statements) . " SQL statements to execute\n\n";
    
    // Execute each statement
    $successCount = 0;
    $errorCount = 0;
    
    foreach ($statements as $index => $statement) {
        $statement = trim($statement);
        
        if (empty($statement)) continue;
        
        try {
            // Skip SELECT statements (they're just for display)
            if (substr(strtoupper($statement), 0, 6) === 'SELECT') {
                $result = $conn->query($statement);
                if ($result) {
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            foreach ($row as $key => $value) {
                                echo "   $key: $value\n";
                            }
                        }
                    }
                }
                continue;
            }
            
            $result = $conn->query($statement);
            
            if ($result) {
                $successCount++;
                
                // Show progress for major operations
                if (substr(strtoupper($statement), 0, 18) === 'INSERT INTO users') {
                    echo "✅ Sample users created\n";
                } elseif (substr(strtoupper($statement), 0, 28) === 'INSERT INTO community_posts') {
                    $affected = $conn->affected_rows;
                    echo "✅ Community posts added ($affected posts)\n";
                } elseif (substr(strtoupper($statement), 0, 27) === 'INSERT INTO post_reactions') {
                    $affected = $conn->affected_rows;
                    echo "✅ Post reactions added ($affected reactions)\n";
                } elseif (substr(strtoupper($statement), 0, 26) === 'INSERT INTO post_comments') {
                    $affected = $conn->affected_rows;
                    echo "✅ Post comments added ($affected comments)\n";
                } elseif (substr(strtoupper($statement), 0, 6) === 'DELETE') {
                    echo "🧹 Cleaned existing data\n";
                } elseif (substr(strtoupper($statement), 0, 11) === 'ALTER TABLE') {
                    echo "🔄 Reset auto increment\n";
                }
            } else {
                $errorCount++;
                echo "❌ Error in statement " . ($index + 1) . ": " . $conn->error . "\n";
                echo "   Statement: " . substr($statement, 0, 100) . "...\n";
            }
            
        } catch (Exception $e) {
            $errorCount++;
            echo "❌ Exception in statement " . ($index + 1) . ": " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n📈 Import Summary:\n";
    echo "   ✅ Successful operations: $successCount\n";
    echo "   ❌ Failed operations: $errorCount\n";
    
    // Final verification
    echo "\n🔍 Verifying import...\n";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM community_posts");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   📝 Total posts in database: " . $row['total'] . "\n";
    }
    
    $result = $conn->query("SELECT post_type, COUNT(*) as count FROM community_posts GROUP BY post_type");
    if ($result) {
        echo "   📊 Posts by category:\n";
        while ($row = $result->fetch_assoc()) {
            echo "      • " . ucfirst($row['post_type']) . ": " . $row['count'] . " posts\n";
        }
    }
    
    $result = $conn->query("SELECT COUNT(*) as total FROM post_reactions");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   👍 Total reactions: " . $row['total'] . "\n";
    }
    
    $result = $conn->query("SELECT COUNT(*) as total FROM post_comments");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "   💬 Total comments: " . $row['total'] . "\n";
    }
    
    echo "\n🎉 Community posts import completed successfully!\n";
    echo "🌐 You can now visit: http://localhost/MoodifyMe/pages/community_posts.php\n";
    echo "📱 Or use the Community dropdown menu to explore the posts!\n\n";
    
    echo "📋 Sample content includes:\n";
    echo "   • General posts about mental health journeys and daily habits\n";
    echo "   • Support posts for people seeking help and advice\n";
    echo "   • Celebration posts sharing victories and milestones\n";
    echo "   • Various mood tags: hopeful, anxious, proud, grateful, etc.\n";
    echo "   • Realistic reactions and supportive comments\n";
    echo "   • Mix of anonymous and public posts\n\n";
    
    echo "💡 Tips:\n";
    echo "   • Use the filter buttons to view posts by category\n";
    echo "   • Try creating your own posts to test the functionality\n";
    echo "   • Check out the reactions and comments on existing posts\n";
    echo "   • Test the search and pagination features\n\n";
    
} catch (Exception $e) {
    echo "💥 Import failed: " . $e->getMessage() . "\n";
    echo "🔧 Please check your database connection and try again.\n";
    exit(1);
}

echo "✨ Happy community building! ✨\n";
?>
