<?php
/**
 * MoodifyMe - Social Actions API
 * Handle AJAX requests for social features
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

// Get action and target user ID
$action = $_POST['action'] ?? '';
$targetUserId = (int)($_POST['user_id'] ?? 0);

// Validate inputs
if (empty($action) || $targetUserId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

// Prevent actions on self
if ($currentUserId == $targetUserId) {
    echo json_encode(['success' => false, 'message' => 'Cannot perform this action on yourself']);
    exit;
}

// Update user's online status
updateUserOnlineStatus($currentUserId);

$response = ['success' => false, 'message' => 'Unknown action'];

switch ($action) {
    case 'follow':
        if (followUser($currentUserId, $targetUserId)) {
            $response = ['success' => true, 'message' => 'User followed successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to follow user or already following'];
        }
        break;
        
    case 'unfollow':
        if (unfollowUser($currentUserId, $targetUserId)) {
            $response = ['success' => true, 'message' => 'User unfollowed successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to unfollow user or not following'];
        }
        break;
        
    case 'connect':
        if (sendConnectionRequest($currentUserId, $targetUserId)) {
            $response = ['success' => true, 'message' => 'Connection request sent successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to send connection request or request already exists'];
        }
        break;
        
    case 'accept_connection':
        if (acceptConnectionRequest($targetUserId, $currentUserId)) {
            $response = ['success' => true, 'message' => 'Connection request accepted'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to accept connection request'];
        }
        break;
        
    case 'decline_connection':
        if (declineConnectionRequest($targetUserId, $currentUserId)) {
            $response = ['success' => true, 'message' => 'Connection request declined'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to decline connection request'];
        }
        break;
        
    case 'remove_connection':
        if (removeConnection($currentUserId, $targetUserId)) {
            $response = ['success' => true, 'message' => 'Connection removed successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to remove connection'];
        }
        break;
        
    case 'block':
        $reason = $_POST['reason'] ?? null;
        if (blockUser($currentUserId, $targetUserId, $reason)) {
            $response = ['success' => true, 'message' => 'User blocked successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to block user'];
        }
        break;
        
    case 'unblock':
        if (unblockUser($currentUserId, $targetUserId)) {
            $response = ['success' => true, 'message' => 'User unblocked successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Failed to unblock user'];
        }
        break;
        
    default:
        $response = ['success' => false, 'message' => 'Invalid action'];
        break;
}

echo json_encode($response);
?>
