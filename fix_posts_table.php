<?php
require_once 'config.php';
require_once 'includes/db_connect.php';

echo "🔧 Fixing community_posts table...\n\n";

try {
    // Check if is_active column exists
    $result = $conn->query("SHOW COLUMNS FROM community_posts LIKE 'is_active'");
    
    if ($result->num_rows == 0) {
        echo "➕ Adding is_active column...\n";
        $sql = "ALTER TABLE community_posts ADD COLUMN is_active BOOLEAN DEFAULT TRUE";
        
        if ($conn->query($sql)) {
            echo "✅ Successfully added is_active column\n";
            
            // Update all existing posts to be active
            $updateSql = "UPDATE community_posts SET is_active = TRUE WHERE is_active IS NULL";
            if ($conn->query($updateSql)) {
                echo "✅ Updated all existing posts to be active\n";
            } else {
                echo "⚠️ Warning: Could not update existing posts: " . $conn->error . "\n";
            }
        } else {
            echo "❌ Error adding is_active column: " . $conn->error . "\n";
        }
    } else {
        echo "✅ is_active column already exists\n";
        
        // Make sure all posts are active
        $updateSql = "UPDATE community_posts SET is_active = TRUE WHERE is_active IS NULL OR is_active = FALSE";
        if ($conn->query($updateSql)) {
            $affected = $conn->affected_rows;
            echo "✅ Updated $affected posts to be active\n";
        }
    }
    
    // Test the query that's used in community_posts.php
    echo "\n🔍 Testing posts query...\n";
    $testQuery = "
        SELECT 
            cp.id,
            cp.title,
            cp.content,
            cp.post_type,
            cp.mood_tag,
            cp.is_anonymous,
            cp.is_active,
            cp.created_at,
            u.username
        FROM community_posts cp
        LEFT JOIN users u ON cp.user_id = u.id
        WHERE cp.is_active = TRUE
        ORDER BY cp.created_at DESC
        LIMIT 5
    ";
    
    $result = $conn->query($testQuery);
    if ($result) {
        echo "✅ Query executed successfully\n";
        echo "📊 Active posts found: " . $result->num_rows . "\n";
        
        if ($result->num_rows > 0) {
            echo "\n📋 Sample active posts:\n";
            while ($row = $result->fetch_assoc()) {
                echo "  - {$row['title']} (Type: {$row['post_type']}, Active: {$row['is_active']})\n";
            }
        }
    } else {
        echo "❌ Query failed: " . $conn->error . "\n";
    }
    
    // Final verification
    echo "\n📊 Final statistics:\n";
    $result = $conn->query("SELECT COUNT(*) as total FROM community_posts");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "  Total posts: " . $row['total'] . "\n";
    }
    
    $result = $conn->query("SELECT COUNT(*) as active FROM community_posts WHERE is_active = TRUE");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "  Active posts: " . $row['active'] . "\n";
    }
    
    echo "\n🎉 Community posts table is now ready!\n";
    echo "🌐 Visit: http://localhost/MoodifyMe/pages/community_posts.php\n";
    
} catch (Exception $e) {
    echo "💥 Error: " . $e->getMessage() . "\n";
}
?>
