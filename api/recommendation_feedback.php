<?php
/**
 * MoodifyMe - Recommendation Feedback API
 * Handles user feedback (likes/dislikes) for recommendations
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

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit;
}

// Get JSON data
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate input
if (!isset($data['recommendation_id']) || !isset($data['feedback_type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Recommendation ID and feedback type are required'
    ]);
    exit;
}

$recommendationId = intval($data['recommendation_id']);
$feedbackType = sanitizeInput($data['feedback_type']);

// Validate feedback type
if ($feedbackType !== 'like' && $feedbackType !== 'dislike') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid feedback type'
    ]);
    exit;
}

// Check if recommendation exists
$stmt = $conn->prepare("SELECT id FROM recommendations WHERE id = ?");
$stmt->bind_param("i", $recommendationId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Recommendation not found'
    ]);
    exit;
}

// Check if user has already provided feedback for this recommendation
$stmt = $conn->prepare("SELECT id, feedback_type FROM recommendation_feedback WHERE user_id = ? AND recommendation_id = ?");
$stmt->bind_param("ii", $userId, $recommendationId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // User has already provided feedback
    $feedback = $result->fetch_assoc();
    
    if ($feedback['feedback_type'] === $feedbackType) {
        // User is submitting the same feedback type, remove it (toggle off)
        $stmt = $conn->prepare("DELETE FROM recommendation_feedback WHERE id = ?");
        $stmt->bind_param("i", $feedback['id']);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Feedback removed',
            'action' => 'removed'
        ]);
    } else {
        // User is changing feedback type, update it
        $stmt = $conn->prepare("UPDATE recommendation_feedback SET feedback_type = ? WHERE id = ?");
        $stmt->bind_param("si", $feedbackType, $feedback['id']);
        $stmt->execute();
        
        echo json_encode([
            'success' => true,
            'message' => 'Feedback updated',
            'action' => 'updated'
        ]);
    }
} else {
    // User has not provided feedback yet, insert new feedback
    $stmt = $conn->prepare("INSERT INTO recommendation_feedback (user_id, recommendation_id, feedback_type, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $userId, $recommendationId, $feedbackType);
    $stmt->execute();
    
    echo json_encode([
        'success' => true,
        'message' => 'Feedback added',
        'action' => 'added'
    ]);
}
?>
