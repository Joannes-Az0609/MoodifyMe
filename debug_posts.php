<?php
// Debug script to test community posts functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require_once 'includes/db_connect.php';

echo "🔍 Debug: Community Posts Query\n\n";

// Simulate the exact query from community_posts.php
$page = 1;
$postsPerPage = 10;
$offset = ($page - 1) * $postsPerPage;
$filterType = 'all';
$whereClause = "WHERE cp.is_active = TRUE";

echo "📊 Query parameters:\n";
echo "  Page: $page\n";
echo "  Posts per page: $postsPerPage\n";
echo "  Offset: $offset\n";
echo "  Filter: $filterType\n";
echo "  Where clause: $whereClause\n\n";

// Test the exact query structure from community_posts.php
try {
    // Check if tables exist
    $postsTableExists = false;
    $reactionsTableExists = false;
    $commentsTableExists = false;

    $result = $conn->query("SHOW TABLES LIKE 'community_posts'");
    if ($result && $result->num_rows > 0) {
        $postsTableExists = true;
        echo "✅ community_posts table exists\n";
    }

    $result = $conn->query("SHOW TABLES LIKE 'post_reactions'");
    if ($result && $result->num_rows > 0) {
        $reactionsTableExists = true;
        echo "✅ post_reactions table exists\n";
    }

    $result = $conn->query("SHOW TABLES LIKE 'post_comments'");
    if ($result && $result->num_rows > 0) {
        $commentsTableExists = true;
        echo "✅ post_comments table exists\n";
    }

    // Build the same query as in community_posts.php
    if ($reactionsTableExists && $commentsTableExists) {
        echo "\n🔍 Using full query with reactions and comments...\n";
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
                COALESCE(reaction_counts.reaction_count, 0) as reaction_count,
                COALESCE(comment_counts.comment_count, 0) as comment_count
            FROM community_posts cp
            LEFT JOIN users u ON cp.user_id = u.id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as reaction_count
                FROM post_reactions
                GROUP BY post_id
            ) reaction_counts ON cp.id = reaction_counts.post_id
            LEFT JOIN (
                SELECT post_id, COUNT(*) as comment_count
                FROM post_comments
                GROUP BY post_id
            ) comment_counts ON cp.id = comment_counts.post_id
            $whereClause
            ORDER BY cp.created_at DESC
            LIMIT $postsPerPage OFFSET $offset
        ";
    } else {
        echo "\n🔍 Using simplified query...\n";
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
            LIMIT $postsPerPage OFFSET $offset
        ";
    }

    echo "\n📝 Executing query...\n";
    $result = $conn->query($query);

    if ($result) {
        $posts = $result->fetch_all(MYSQLI_ASSOC);
        echo "✅ Query executed successfully\n";
        echo "📊 Posts returned: " . count($posts) . "\n\n";

        if (!empty($posts)) {
            echo "📋 Posts found:\n";
            foreach ($posts as $i => $post) {
                echo "  " . ($i + 1) . ". {$post['title']}\n";
                echo "     Type: {$post['post_type']}\n";
                echo "     Author: " . ($post['is_anonymous'] ? 'Anonymous' : $post['username']) . "\n";
                echo "     Created: {$post['created_at']}\n";
                echo "     Reactions: {$post['reaction_count']}, Comments: {$post['comment_count']}\n\n";
            }
        } else {
            echo "⚠️ No posts returned by query\n";
            
            // Check if there are any posts at all
            $totalResult = $conn->query("SELECT COUNT(*) as total FROM community_posts");
            if ($totalResult) {
                $totalRow = $totalResult->fetch_assoc();
                echo "📊 Total posts in database: " . $totalRow['total'] . "\n";
            }
            
            // Check active posts
            $activeResult = $conn->query("SELECT COUNT(*) as active FROM community_posts WHERE is_active = TRUE");
            if ($activeResult) {
                $activeRow = $activeResult->fetch_assoc();
                echo "📊 Active posts in database: " . $activeRow['active'] . "\n";
            }
        }
    } else {
        echo "❌ Query failed: " . $conn->error . "\n";
    }

} catch (Exception $e) {
    echo "💥 Exception: " . $e->getMessage() . "\n";
}

echo "\n🎯 Debug complete!\n";
?>
