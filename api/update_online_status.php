<?php
/**
 * MoodifyMe - Update Online Status API
 * Update user's online status
 */

// Include configuration and functions
require_once '../config.php';
require_once '../includes/functions.php';
require_once '../includes/db_connect.php';
require_once '../includes/social_functions.php';

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

$currentUserId = $_SESSION['user_id'];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get status parameter (default to 'online')
$status = $_POST['status'] ?? 'online';

// Validate status
$validStatuses = ['online', 'away', 'busy', 'offline'];
if (!in_array($status, $validStatuses)) {
    $status = 'online';
}

// Update user's online status
if (updateUserOnlineStatus($currentUserId, $status)) {
    echo json_encode([
        'success' => true,
        'message' => 'Online status updated',
        'status' => $status
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update online status'
    ]);
}
?>
