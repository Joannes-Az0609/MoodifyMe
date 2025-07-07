<?php
/**
 * MoodifyMe - Community Stats API
 * Returns community statistics and unread message counts for the floating widget
 */

require_once '../config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Start session
session_start();

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get unread direct messages count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_messages 
        FROM direct_messages 
        WHERE receiver_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadMessages = $result->fetch_assoc()['unread_messages'];

    // Get unread notifications count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as unread_notifications 
        FROM notifications 
        WHERE user_id = ? AND is_read = 0
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $unreadNotifications = $result->fetch_assoc()['unread_notifications'];

    // Get total community activity (new posts in last 24 hours)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as new_posts 
        FROM community_posts 
        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $newPosts = $result->fetch_assoc()['new_posts'];

    // Get active chat rooms count
    $stmt = $conn->prepare("
        SELECT COUNT(*) as active_rooms 
        FROM chat_rooms 
        WHERE is_active = 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $activeRooms = $result->fetch_assoc()['active_rooms'];

    // Calculate total unread items for badge
    $totalUnread = $unreadMessages + $unreadNotifications;

    echo json_encode([
        'success' => true,
        'unread_messages' => $totalUnread,
        'direct_messages' => $unreadMessages,
        'notifications' => $unreadNotifications,
        'new_posts' => $newPosts,
        'active_rooms' => $activeRooms,
        'stats' => [
            'total_unread' => $totalUnread,
            'community_activity' => $newPosts,
            'active_chat_rooms' => $activeRooms
        ]
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to get community stats',
        'unread_messages' => 0
    ]);
}
?>
