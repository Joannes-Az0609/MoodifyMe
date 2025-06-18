<?php
/**
 * MoodifyMe - Delete Account API
 * Permanently deletes a user account and all associated data
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

// Check if confirmation is provided
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['delete_confirmation']) || $_POST['delete_confirmation'] !== 'DELETE') {
    // Redirect to profile page with error
    redirect(APP_URL . '/pages/profile.php?error=invalid_confirmation');
}

// Start transaction
$conn->begin_transaction();

try {
    // Delete user preferences
    $stmt = $conn->prepare("DELETE FROM user_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Delete recommendation feedback
    $stmt = $conn->prepare("DELETE FROM recommendation_feedback WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Delete recommendation logs
    $stmt = $conn->prepare("DELETE FROM recommendation_logs WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Delete emotions
    $stmt = $conn->prepare("DELETE FROM emotions WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Delete user sessions
    $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Delete password reset tokens
    $stmt = $conn->prepare("DELETE FROM password_reset_tokens WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Delete user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Commit transaction
    $conn->commit();
    
    // Clear session
    session_unset();
    session_destroy();
    
    // Redirect to home page with success message
    redirect(APP_URL . '?success=account_deleted');
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Redirect to profile page with error message
    redirect(APP_URL . '/pages/profile.php?error=delete_failed');
}
?>
