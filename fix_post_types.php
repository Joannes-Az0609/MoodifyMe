<?php
require_once 'config.php';
require_once 'includes/db_connect.php';

echo "🔧 Fixing post_type values...\n\n";

// Get posts with empty post_type
$result = $conn->query("SELECT id, title FROM community_posts WHERE post_type = '' OR post_type IS NULL");

if ($result && $result->num_rows > 0) {
    echo "📝 Found posts with empty post_type:\n";
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        
        // Determine post type based on title content
        $postType = 'general'; // default
        
        if (strpos($title, 'Milestone') !== false || 
            strpos($title, 'Asked for Help') !== false || 
            strpos($title, 'Anxiety-Free') !== false || 
            strpos($title, 'Boundary') !== false || 
            strpos($title, 'Meditation') !== false ||
            strpos($title, '!') !== false) {
            $postType = 'celebration';
        }
        
        // Update the post
        $stmt = $conn->prepare("UPDATE community_posts SET post_type = ? WHERE id = ?");
        $stmt->bind_param("si", $postType, $id);
        
        if ($stmt->execute()) {
            echo "  ✅ Updated post ID $id: '$title' -> $postType\n";
        } else {
            echo "  ❌ Failed to update post ID $id: " . $stmt->error . "\n";
        }
    }
} else {
    echo "✅ No posts with empty post_type found\n";
}

// Verify the fix
echo "\n🔍 Verification:\n";
$result = $conn->query("SELECT post_type, COUNT(*) as count FROM community_posts GROUP BY post_type");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "  {$row['post_type']}: {$row['count']} posts\n";
    }
}

echo "\n🎉 Post types fixed!\n";
?>
