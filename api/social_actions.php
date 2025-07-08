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
        // Add detailed debugging for follow action
        error_log("DEBUG: Attempting to follow user - currentUserId: $currentUserId, targetUserId: $targetUserId");

        // Check if user_follows table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'user_follows'");
        if ($tableCheck->num_rows === 0) {
            error_log("DEBUG: user_follows table does not exist");
            $response = ['success' => false, 'message' => 'Follow functionality not available - missing database table'];
        } else {
            // Check if already following
            $checkStmt = $conn->prepare("SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ?");
            $checkStmt->bind_param("ii", $currentUserId, $targetUserId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                error_log("DEBUG: User $currentUserId is already following user $targetUserId");
                $response = ['success' => false, 'message' => 'You are already following this user'];
            } else {
                // Try to follow the user
                if (followUser($currentUserId, $targetUserId)) {
                    $response = ['success' => true, 'message' => 'User followed successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to follow user - check error logs for details'];
                }
            }
        }
        break;
        
    case 'unfollow':
        // Add detailed debugging for unfollow action
        error_log("DEBUG: Attempting to unfollow user - currentUserId: $currentUserId, targetUserId: $targetUserId");

        // Check if user_follows table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'user_follows'");
        if ($tableCheck->num_rows === 0) {
            error_log("DEBUG: user_follows table does not exist");
            $response = ['success' => false, 'message' => 'Unfollow functionality not available - missing database table'];
        } else {
            // Check if currently following
            $checkStmt = $conn->prepare("SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ?");
            $checkStmt->bind_param("ii", $currentUserId, $targetUserId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows === 0) {
                error_log("DEBUG: User $currentUserId is not following user $targetUserId");
                $response = ['success' => false, 'message' => 'You are not following this user'];
            } else {
                // Try to unfollow the user
                if (unfollowUser($currentUserId, $targetUserId)) {
                    $response = ['success' => true, 'message' => 'User unfollowed successfully'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to unfollow user - check error logs for details'];
                }
            }
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
        // Add detailed debugging
        error_log("DEBUG: Attempting to accept connection - targetUserId: $targetUserId, currentUserId: $currentUserId");

        // Check if the connection request exists
        $checkStmt = $conn->prepare("
            SELECT id, status FROM user_connections
            WHERE requester_id = ? AND receiver_id = ?
        ");
        $checkStmt->bind_param("ii", $targetUserId, $currentUserId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows === 0) {
            error_log("DEBUG: No connection found between users $targetUserId and $currentUserId");
            $response = ['success' => false, 'message' => 'No connection request found between these users'];
        } else {
            $connectionData = $checkResult->fetch_assoc();
            error_log("DEBUG: Found connection - ID: " . $connectionData['id'] . ", Status: " . $connectionData['status']);

            if ($connectionData['status'] !== 'pending') {
                $response = ['success' => false, 'message' => 'Connection request is not pending (current status: ' . $connectionData['status'] . ')'];
            } else {
                // Try to accept the connection
                if (acceptConnectionRequest($targetUserId, $currentUserId)) {
                    $response = ['success' => true, 'message' => 'Connection request accepted'];
                } else {
                    $response = ['success' => false, 'message' => 'Failed to accept connection request - check error logs for details'];
                }
            }
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
