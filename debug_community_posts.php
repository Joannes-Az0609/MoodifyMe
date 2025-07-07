<?php
session_start();

// Set session for testing
$_SESSION['user_id'] = 6;
$_SESSION['username'] = 'Jo';

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/db_connect.php';

echo "🔍 Debugging Community Posts Query\n\n";

// Simulate the exact conditions from community_posts.php
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$postsPerPage = 10;
$offset = ($page - 1) * $postsPerPage;

$filterType = $_GET['filter'] ?? 'all';
$whereClause = "WHERE cp.is_active = TRUE";
$params = [];
$types = "";

if ($filterType !== 'all') {
    $whereClause .= " AND cp.post_type = ?";
    $params[] = $filterType;
    $types .= "s";
}

echo "📊 Query Setup:\n";
echo "  Page: $page\n";
echo "  Posts per page: $postsPerPage\n";
echo "  Offset: $offset\n";
echo "  Filter: $filterType\n";
echo "  Where clause: $whereClause\n";
echo "  Params: " . json_encode($params) . "\n";
echo "  Types: $types\n\n";

// Check table existence (same logic as community_posts.php)
try {
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

    // Build query exactly like community_posts.php
    if ($reactionsTableExists && $commentsTableExists) {
        echo "\n🔍 Using full query with reactions and comments...\n";
        $stmt = $conn->prepare("
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
            LIMIT ? OFFSET ?
        ");
        
        $finalParams = array_merge($params, [$postsPerPage, $offset]);
        $finalTypes = $types . "ii";
        
    } else {
        echo "\n🔍 Using simplified query...\n";
        $stmt = $conn->prepare("
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
            LIMIT ? OFFSET ?
        ");
        
        $finalParams = array_merge($params, [$postsPerPage, $offset]);
        $finalTypes = $types . "ii";
    }

    echo "📝 Final params: " . json_encode($finalParams) . "\n";
    echo "📝 Final types: $finalTypes\n\n";

    if (!empty($finalParams)) {
        $stmt->bind_param($finalTypes, ...$finalParams);
    }
    
    echo "🚀 Executing query...\n";
    $stmt->execute();
    $result = $stmt->get_result();
    $posts = $result->fetch_all(MYSQLI_ASSOC);

    echo "✅ Query executed successfully\n";
    echo "📊 Posts returned: " . count($posts) . "\n\n";

    if (!empty($posts)) {
        echo "📋 Posts found:\n";
        foreach ($posts as $i => $post) {
            echo "  " . ($i + 1) . ". {$post['title']}\n";
            echo "     Type: {$post['post_type']}\n";
            echo "     Author: " . ($post['is_anonymous'] ? 'Anonymous' : $post['username']) . "\n";
            echo "     Active: " . (isset($post['is_active']) ? $post['is_active'] : 'N/A') . "\n\n";
        }
    } else {
        echo "⚠️ No posts returned!\n\n";
        
        // Debug: Check what's in the database
        echo "🔍 Database debugging:\n";
        $debugResult = $conn->query("SELECT COUNT(*) as total FROM community_posts");
        if ($debugResult) {
            $row = $debugResult->fetch_assoc();
            echo "  Total posts in DB: " . $row['total'] . "\n";
        }
        
        $debugResult = $conn->query("SELECT COUNT(*) as active FROM community_posts WHERE is_active = TRUE");
        if ($debugResult) {
            $row = $debugResult->fetch_assoc();
            echo "  Active posts in DB: " . $row['active'] . "\n";
        }
        
        $debugResult = $conn->query("SELECT COUNT(*) as active FROM community_posts WHERE is_active = 1");
        if ($debugResult) {
            $row = $debugResult->fetch_assoc();
            echo "  Active posts (=1) in DB: " . $row['active'] . "\n";
        }
        
        // Test simple query
        echo "\n🔍 Testing simple query:\n";
        $simpleResult = $conn->query("SELECT id, title, is_active FROM community_posts ORDER BY created_at DESC LIMIT 3");
        if ($simpleResult) {
            while ($row = $simpleResult->fetch_assoc()) {
                echo "  ID: {$row['id']}, Title: {$row['title']}, Active: {$row['is_active']}\n";
            }
        }
    }

} catch (Exception $e) {
    echo "💥 Exception: " . $e->getMessage() . "\n";
    echo "📍 Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎯 Debug complete!\n";
?>
