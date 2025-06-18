<?php
/**
 * MoodifyMe - Save Emotion API
 * Saves detected emotion to database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['emotion_type'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Emotion type required']);
    exit;
}

$userId = $_SESSION['user_id'];
$emotionType = $input['emotion_type'];
$confidence = $input['confidence'] ?? 0.5;
$source = $input['source'] ?? 'manual';
$rawInput = $input['raw_input'] ?? '';

// Validate emotion type
$validEmotions = array_keys(EMOTION_CATEGORIES);
if (!in_array($emotionType, $validEmotions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid emotion type']);
    exit;
}

try {
    // Save emotion to database
    $stmt = $conn->prepare("
        INSERT INTO emotions (user_id, emotion_type, confidence, source, raw_input, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param("isdss", $userId, $emotionType, $confidence, $source, $rawInput);
    
    if ($stmt->execute()) {
        $emotionId = $conn->insert_id;
        
        echo json_encode([
            'success' => true,
            'emotion_id' => $emotionId,
            'emotion_type' => $emotionType,
            'confidence' => $confidence,
            'source' => $source
        ]);
    } else {
        throw new Exception("Failed to save emotion: " . $stmt->error);
    }
    
} catch (Exception $e) {
    error_log("Save emotion error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to save emotion'
    ]);
}
?>
