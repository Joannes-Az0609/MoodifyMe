<?php
/**
 * MoodifyMe - Delete Emotion API
 * Deletes an emotion entry and associated data
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

// Get emotion ID from URL parameter
$emotionId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Check if emotion ID is valid
if ($emotionId <= 0) {
    // Redirect to history page with error
    redirect(APP_URL . '/pages/history.php?error=invalid_id');
}

// Check if emotion belongs to user
$stmt = $conn->prepare("SELECT id FROM emotions WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $emotionId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Emotion not found or doesn't belong to user
    redirect(APP_URL . '/pages/history.php?error=not_found');
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete recommendation logs associated with this emotion
    $stmt = $conn->prepare("DELETE FROM recommendation_logs WHERE emotion_id = ?");
    $stmt->bind_param("i", $emotionId);
    $stmt->execute();
    
    // Delete the emotion
    $stmt = $conn->prepare("DELETE FROM emotions WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $emotionId, $userId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Redirect to history page with success message
    redirect(APP_URL . '/pages/history.php?success=deleted');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Redirect to history page with error message
    redirect(APP_URL . '/pages/history.php?error=delete_failed');
}
?>
