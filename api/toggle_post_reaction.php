<?php
/**
 * MoodifyMe - Toggle Post Reaction API
 * Handle likes, hearts, support reactions on community posts
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
$reactionType = $input['reaction_type'] ?? '';

// Validate input
if ($postId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit;
}

$validReactions = ['like', 'heart', 'support', 'hug', 'celebrate'];
if (!in_array($reactionType, $validReactions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid reaction type']);
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
    
    // Check if user already reacted with this type
    $stmt = $conn->prepare("
        SELECT id FROM post_reactions 
        WHERE post_id = ? AND user_id = ? AND reaction_type = ?
    ");
    $stmt->bind_param("iis", $postId, $userId, $reactionType);
    $stmt->execute();
    $existingReaction = $stmt->get_result();
    
    if ($existingReaction->num_rows > 0) {
        // Remove reaction (toggle off)
        $stmt = $conn->prepare("
            DELETE FROM post_reactions 
            WHERE post_id = ? AND user_id = ? AND reaction_type = ?
        ");
        $stmt->bind_param("iis", $postId, $userId, $reactionType);
        $stmt->execute();
        $userReacted = false;
    } else {
        // Add reaction (toggle on)
        $stmt = $conn->prepare("
            INSERT INTO post_reactions (post_id, user_id, reaction_type) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iis", $postId, $userId, $reactionType);
        $stmt->execute();
        $userReacted = true;
    }
    
    // Get updated reaction count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM post_reactions 
        WHERE post_id = ?
    ");
    $stmt->bind_param("i", $postId);
    $stmt->execute();
    $result = $stmt->get_result();
    $reactionCount = $result->fetch_assoc()['count'];
    
    echo json_encode([
        'success' => true,
        'user_reacted' => $userReacted,
        'reaction_count' => (int)$reactionCount,
        'message' => $userReacted ? 'Reaction added' : 'Reaction removed'
    ]);
    
} catch (Exception $e) {
    error_log("Toggle post reaction error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
