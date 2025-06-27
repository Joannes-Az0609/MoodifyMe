<?php
/**
 * MoodifyMe - Get Post Comments API
 * Retrieve comments for a specific community post
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include configuration and database
require_once '../config.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

$postId = (int)($_GET['post_id'] ?? 0);

// Validate input
if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

try {
    // Check if post exists
    $stmt = $conn->prepare("SELECT id FROM community_posts WHERE id = ? AND is_active = TRUE");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit;
    }
    
    // Get comments
    $stmt = $conn->prepare("
        SELECT pc.*, u.username
        FROM post_comments pc
        LEFT JOIN users u ON pc.user_id = u.id
        WHERE pc.post_id = ? AND pc.is_active = TRUE
        ORDER BY pc.created_at ASC
    ");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $comments = [];
    while ($row = $result->fetch_assoc()) {
        $comments[] = [
            'id' => $row['id'],
            'content' => htmlspecialchars($row['content']),
            'username' => $row['username'],
            'is_anonymous' => (bool)$row['is_anonymous'],
            'created_at' => $row['created_at'],
            'time_ago' => timeAgo($row['created_at'])
        ];
    }
    
    echo json_encode([
        'success' => true,
        'comments' => $comments
    ]);
    
} catch (Exception $e) {
    error_log("Get post comments error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}

function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . 'm ago';
    if ($time < 86400) return floor($time/3600) . 'h ago';
    if ($time < 2592000) return floor($time/86400) . 'd ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>
