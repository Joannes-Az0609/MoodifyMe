<?php
/**
 * MoodifyMe - Notification Count API
 * Returns unread notification count for current user
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/notification_functions.php';

// Set JSON header
header('Content-Type: application/json');

// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get unread notification count
    $count = getUnreadNotificationCount($userId);
    
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get notification count'
    ]);
}
?>
