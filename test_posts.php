<?php
require_once 'config.php';
require_once 'includes/db_connect.php';

echo "Testing database connection and posts...\n\n";

// Check if community_posts table exists
$result = $conn->query("SHOW TABLES LIKE 'community_posts'");
if ($result && $result->num_rows > 0) {
    echo "✅ community_posts table exists\n";
} else {
    echo "❌ community_posts table does not exist\n";
    exit(1);
}

// Count total posts
$result = $conn->query("SELECT COUNT(*) as count FROM community_posts");
if ($result) {
    $row = $result->fetch_assoc();
    echo "📊 Total posts in database: " . $row['count'] . "\n";
} else {
    echo "❌ Error counting posts: " . $conn->error . "\n";
}

// Get sample posts
$result = $conn->query("SELECT id, title, post_type, is_active FROM community_posts LIMIT 5");
if ($result) {
    echo "\n📝 Sample posts:\n";
    while ($row = $result->fetch_assoc()) {
        echo "  ID: {$row['id']}, Title: {$row['title']}, Type: {$row['post_type']}, Active: {$row['is_active']}\n";
    }
} else {
    echo "❌ Error fetching posts: " . $conn->error . "\n";
}

// Test the exact query from community_posts.php
echo "\n🔍 Testing community posts query...\n";
$whereClause = "WHERE cp.is_active = TRUE";
$query = "
    SELECT 
        cp.id,
        cp.title,
        cp.content,
        cp.post_type,
        cp.mood_tag,
        cp.is_anonymous,
        cp.created_at,
        u.username,
        0 as reaction_count,
        0 as comment_count
    FROM community_posts cp
    LEFT JOIN users u ON cp.user_id = u.id
    $whereClause
    ORDER BY cp.created_at DESC
    LIMIT 10
";

$result = $conn->query($query);
if ($result) {
    echo "✅ Query executed successfully\n";
    echo "📊 Rows returned: " . $result->num_rows . "\n";
    
    if ($result->num_rows > 0) {
        echo "\n📋 Posts found:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  - {$row['title']} (Type: {$row['post_type']}, Active: " . ($row['is_active'] ?? 'NULL') . ")\n";
        }
    } else {
        echo "⚠️ No posts returned by query\n";
    }
} else {
    echo "❌ Query failed: " . $conn->error . "\n";
}

// Check is_active column
echo "\n🔍 Checking is_active column...\n";
$result = $conn->query("DESCRIBE community_posts");
if ($result) {
    $hasIsActive = false;
    while ($row = $result->fetch_assoc()) {
        if ($row['Field'] === 'is_active') {
            $hasIsActive = true;
            echo "✅ is_active column exists: {$row['Type']}, Default: {$row['Default']}\n";
            break;
        }
    }
    if (!$hasIsActive) {
        echo "❌ is_active column does not exist\n";
    }
} else {
    echo "❌ Error describing table: " . $conn->error . "\n";
}
?>
