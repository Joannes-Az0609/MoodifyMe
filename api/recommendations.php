<?php
/**
 * MoodifyMe - Recommendations API
 * Returns recommendations based on source and target emotions
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'User not authenticated'
    ]);
    exit;
}

// Get user ID
$userId = $_SESSION['user_id'];

// Set response header
header('Content-Type: application/json');

// Get source and target emotions from URL parameters
$sourceEmotion = isset($_GET['source']) ? sanitizeInput($_GET['source']) : '';
$targetEmotion = isset($_GET['target']) ? sanitizeInput($_GET['target']) : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$filter = isset($_GET['filter']) ? sanitizeInput($_GET['filter']) : 'all';
$limit = 6; // Number of recommendations per page
$offset = ($page - 1) * $limit;

// Validate input
if (empty($sourceEmotion) || empty($targetEmotion)) {
    echo json_encode([
        'success' => false,
        'message' => 'Source and target emotions are required'
    ]);
    exit;
}

// Get recommendations
$recommendations = [];

// Build query
$query = "SELECT * FROM recommendations WHERE source_emotion = ? AND target_emotion = ?";

// Add filter if not 'all'
if ($filter !== 'all') {
    $query .= " AND type = ?";
}

// Add pagination
$query .= " ORDER BY RAND() LIMIT ? OFFSET ?";

// Prepare statement
if ($filter === 'all') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssii", $sourceEmotion, $targetEmotion, $limit, $offset);
} else {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssii", $sourceEmotion, $targetEmotion, $filter, $limit, $offset);
}

// Execute query
$stmt->execute();
$result = $stmt->get_result();

// Get recommendations
while ($row = $result->fetch_assoc()) {
    // Get likes and dislikes
    $likesStmt = $conn->prepare("SELECT COUNT(*) as count FROM recommendation_feedback WHERE recommendation_id = ? AND feedback_type = 'like'");
    $likesStmt->bind_param("i", $row['id']);
    $likesStmt->execute();
    $likesResult = $likesStmt->get_result();
    $likes = $likesResult->fetch_assoc()['count'];
    
    $dislikesStmt = $conn->prepare("SELECT COUNT(*) as count FROM recommendation_feedback WHERE recommendation_id = ? AND feedback_type = 'dislike'");
    $dislikesStmt->bind_param("i", $row['id']);
    $dislikesStmt->execute();
    $dislikesResult = $dislikesStmt->get_result();
    $dislikes = $dislikesResult->fetch_assoc()['count'];
    
    // Add likes and dislikes to recommendation
    $row['likes'] = $likes;
    $row['dislikes'] = $dislikes;
    
    // Add recommendation to array
    $recommendations[] = $row;
}

// Log recommendation view
if (!empty($recommendations)) {
    // Get latest emotion
    $stmt = $conn->prepare("SELECT id FROM emotions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $emotionId = $result->fetch_assoc()['id'];
        
        // Log recommendation views
        foreach ($recommendations as $recommendation) {
            logRecommendationView($userId, $emotionId, $recommendation['id']);
        }
    }
}

// Return response
echo json_encode([
    'success' => true,
    'recommendations' => $recommendations,
    'page' => $page,
    'filter' => $filter
]);
?>
