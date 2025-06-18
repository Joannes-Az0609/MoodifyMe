<?php
/**
 * MoodifyMe - Save Facial Emotion Handler
 * Handles form submission from facial detection and saves emotion to database
 */

require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    redirect(APP_URL . '/pages/login.php');
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(APP_URL . '/pages/landmark-emotion.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Get form data
$emotionType = $_POST['emotion_type'] ?? '';
$confidence = floatval($_POST['confidence'] ?? 0.5);
$source = $_POST['source'] ?? 'face';
$rawInput = $_POST['raw_input'] ?? '';
$targetMood = $_POST['target_mood'] ?? '';
$method = $_POST['method'] ?? 'landmark_detection';

// Debug logging
error_log("Save facial emotion - POST data: " . print_r($_POST, true));
error_log("Emotion type: $emotionType, Target mood: $targetMood, Confidence: $confidence");

// Validate emotion type
$validEmotions = array_keys(EMOTION_CATEGORIES);
if (!in_array($emotionType, $validEmotions)) {
    // Debug log
    error_log("Invalid emotion type: $emotionType. Valid emotions: " . implode(', ', $validEmotions));
    // Redirect back with error
    redirect(APP_URL . '/pages/landmark-emotion.php?error=invalid_emotion');
    exit;
}

try {
    // Save emotion to database using the working logEmotion function
    $emotionId = logEmotion($userId, $emotionType, $confidence, $source, $rawInput);
    
    if ($emotionId) {
        // Success! Redirect to mood options with all the parameters
        $params = http_build_query([
            'source' => $emotionType,
            'target' => $targetMood,
            'confidence' => $confidence,
            'method' => $method,
            'emotion_id' => $emotionId
        ]);

        $redirectUrl = APP_URL . '/pages/mood_options.php?' . $params;
        error_log("Redirecting to: $redirectUrl");
        redirect($redirectUrl);
    } else {
        // Failed to save emotion
        error_log("Failed to save emotion to database");
        redirect(APP_URL . '/pages/landmark-emotion.php?error=save_failed');
    }
    
} catch (Exception $e) {
    // Log error and redirect
    error_log("Save facial emotion error: " . $e->getMessage());
    redirect(APP_URL . '/pages/landmark-emotion.php?error=database_error');
}
?>
