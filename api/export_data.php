<?php
/**
 * MoodifyMe - Export User Data API
 * Exports all user data in JSON format
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    redirect(APP_URL . '/pages/login.php');
}

// Get user ID
$userId = $_SESSION['user_id'];

// Get user data
$userData = [];

// Get user profile
$stmt = $conn->prepare("SELECT id, username, email, bio, profile_image, created_at, updated_at, last_login FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $userData['profile'] = $result->fetch_assoc();
}

// Get user preferences
$userData['preferences'] = [];
$stmt = $conn->prepare("SELECT preference_key, preference_value, created_at, updated_at FROM user_preferences WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $userData['preferences'][] = $row;
}

// Get emotions
$userData['emotions'] = [];
$stmt = $conn->prepare("SELECT id, emotion_type, confidence, source, raw_input, created_at FROM emotions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $userData['emotions'][] = $row;
}

// Get recommendation logs
$userData['recommendation_logs'] = [];
$stmt = $conn->prepare("
    SELECT rl.id, rl.emotion_id, rl.recommendation_id, rl.viewed_at, 
           r.title, r.type, r.source_emotion, r.target_emotion
    FROM recommendation_logs rl
    JOIN recommendations r ON rl.recommendation_id = r.id
    WHERE rl.user_id = ?
    ORDER BY rl.viewed_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $userData['recommendation_logs'][] = $row;
}

// Get recommendation feedback
$userData['recommendation_feedback'] = [];
$stmt = $conn->prepare("
    SELECT rf.id, rf.recommendation_id, rf.feedback_type, rf.created_at,
           r.title, r.type
    FROM recommendation_feedback rf
    JOIN recommendations r ON rf.recommendation_id = r.id
    WHERE rf.user_id = ?
    ORDER BY rf.created_at DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $userData['recommendation_feedback'][] = $row;
}

// Set headers for JSON download
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="moodifyme_data_export_' . date('Y-m-d') . '.json"');

// Output JSON data
echo json_encode($userData, JSON_PRETTY_PRINT);
?>
