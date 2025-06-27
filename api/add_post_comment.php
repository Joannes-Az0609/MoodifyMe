<?php
/**
 * MoodifyMe - Add Post Comment API
 * Add a new comment to a community post
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
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

$userId = $_SESSION['user_id'];

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit;
}

$postId = (int)($input['post_id'] ?? 0);
$content = trim($input['content'] ?? '');
$isAnonymous = isset($input['is_anonymous']) ? (bool)$input['is_anonymous'] : false;

// Validate input
if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Comment content cannot be empty']);
    exit;
}

if (strlen($content) > 1000) {
    echo json_encode(['success' => false, 'message' => 'Comment is too long (max 1000 characters)']);
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
    
    // Add comment
    $stmt = $conn->prepare("
        INSERT INTO post_comments (post_id, user_id, content, is_anonymous) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->bind_param("iisi", $postId, $userId, $content, $isAnonymous);
    
    if ($stmt->execute()) {
        $commentId = $conn->insert_id;
        
        // Get the new comment count
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM post_comments 
            WHERE post_id = ? AND is_active = TRUE
        ");
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $result = $stmt->get_result();
        $commentCount = $result->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true,
            'comment_id' => $commentId,
            'comment_count' => (int)$commentCount,
            'message' => 'Comment added successfully'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add comment']);
    }
    
} catch (Exception $e) {
    error_log("Add post comment error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
