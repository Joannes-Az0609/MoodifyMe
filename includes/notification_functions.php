<?php
/**
 * MoodifyMe - Notification System Functions
 * Comprehensive notification management system
 */

require_once 'db_connect.php';

/**
 * Create a new notification
 */
function createNotification($userId, $type, $title, $message, $options = []) {
    global $conn;
    
    $data = isset($options['data']) ? json_encode($options['data']) : null;
    $actionUrl = $options['action_url'] ?? null;
    $actionText = $options['action_text'] ?? null;
    $relatedUserId = $options['related_user_id'] ?? null;
    $relatedItemId = $options['related_item_id'] ?? null;

    $stmt = $conn->prepare("
        INSERT INTO notifications (user_id, type, title, message, data, action_url, action_text, related_user_id, related_item_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param("issssssii", $userId, $type, $title, $message, $data, $actionUrl, $actionText, $relatedUserId, $relatedItemId);
    
    return $stmt->execute();
}

/**
 * Get user notifications with pagination
 */
function getUserNotifications($userId, $limit = 20, $offset = 0, $unreadOnly = false) {
    global $conn;

    // Check if notifications table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        return []; // Return empty array if table doesn't exist
    }

    $whereClause = "WHERE n.user_id = ?";
    if ($unreadOnly) {
        $whereClause .= " AND n.is_read = FALSE";
    }

    $sql = "
        SELECT n.*,
               u.username as related_username,
               u.profile_picture as related_user_image
        FROM notifications n
        LEFT JOIN users u ON n.related_user_id = u.id
        $whereClause
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare notification query: " . $conn->error);
        return [];
    }

    $stmt->bind_param("iii", $userId, $limit, $offset);
    if (!$stmt->execute()) {
        error_log("Failed to execute notification query: " . $stmt->error);
        return [];
    }

    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

/**
 * Get unread notification count for user
 */
function getUnreadNotificationCount($userId) {
    global $conn;

    // Check if notifications table exists first
    $tableCheck = $conn->query("SHOW TABLES LIKE 'notifications'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        return 0; // Return 0 if table doesn't exist
    }

    try {
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count
            FROM notifications
            WHERE user_id = ? AND is_read = FALSE
        ");

        if (!$stmt) {
            error_log("Failed to prepare notification count query: " . $conn->error);
            return 0;
        }

        $stmt->bind_param("i", $userId);

        if (!$stmt->execute()) {
            error_log("Failed to execute notification count query: " . $stmt->error);
            return 0;
        }

        $result = $stmt->get_result();
        if (!$result) {
            return 0;
        }

        $row = $result->fetch_assoc();
        return $row['count'] ?? 0;

    } catch (Exception $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($notificationId, $userId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notificationId, $userId);
    
    return $stmt->execute();
}

/**
 * Mark all notifications as read for user
 */
function markAllNotificationsAsRead($userId) {
    global $conn;
    
    $stmt = $conn->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ? AND is_read = FALSE");
    $stmt->bind_param("i", $userId);
    
    return $stmt->execute();
}

/**
 * Delete notification
 */
function deleteNotification($notificationId, $userId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $notificationId, $userId);
    
    return $stmt->execute();
}

/**
 * Create friend request notification
 */
function createFriendRequestNotification($receiverId, $requesterId) {
    global $conn;
    
    // Get requester info
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $requesterId);
    $stmt->execute();
    $requester = $stmt->get_result()->fetch_assoc();
    
    if (!$requester) return false;
    
    return createNotification(
        $receiverId,
        'friend_request',
        'New Friend Request',
        $requester['username'] . ' wants to connect with you',
        [
            'related_user_id' => $requesterId,
            'action_url' => '/pages/connections.php?tab=requests',
            'action_text' => 'View Request',
            'data' => ['requester_id' => $requesterId]
        ]
    );
}

/**
 * Create friend request accepted notification
 */
function createFriendAcceptedNotification($requesterId, $accepterId) {
    global $conn;
    
    // Get accepter info
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $accepterId);
    $stmt->execute();
    $accepter = $stmt->get_result()->fetch_assoc();
    
    if (!$accepter) return false;
    
    return createNotification(
        $requesterId,
        'friend_accepted',
        'Friend Request Accepted',
        $accepter['username'] . ' accepted your friend request',
        [
            'related_user_id' => $accepterId,
            'action_url' => '/pages/messages.php?user=' . $accepterId,
            'action_text' => 'Send Message',
            'data' => ['accepter_id' => $accepterId]
        ]
    );
}

/**
 * Create new message notification
 */
function createMessageNotification($receiverId, $senderId, $messagePreview) {
    global $conn;
    
    // Get sender info
    $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->bind_param("i", $senderId);
    $stmt->execute();
    $sender = $stmt->get_result()->fetch_assoc();
    
    if (!$sender) return false;
    
    // Truncate message preview
    $preview = strlen($messagePreview) > 50 ? substr($messagePreview, 0, 50) . '...' : $messagePreview;
    
    return createNotification(
        $receiverId,
        'message',
        'New Message',
        $sender['username'] . ': ' . $preview,
        [
            'related_user_id' => $senderId,
            'action_url' => '/pages/messages.php?user=' . $senderId,
            'action_text' => 'Reply',
            'data' => ['sender_id' => $senderId]
        ]
    );
}

/**
 * Get notification preferences for user
 */
function getNotificationPreferences($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM notification_preferences WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    $result = $stmt->get_result()->fetch_assoc();
    
    // Return default preferences if none exist
    if (!$result) {
        return [
            'friend_requests' => true,
            'friend_accepted' => true,
            'messages' => true,
            'system_updates' => true,
            'mood_milestones' => true,
            'recommendations' => true,
            'email_notifications' => false,
            'push_notifications' => true
        ];
    }
    
    return $result;
}

/**
 * Update notification preferences
 */
function updateNotificationPreferences($userId, $preferences) {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO notification_preferences 
        (user_id, friend_requests, friend_accepted, messages, system_updates, mood_milestones, recommendations, email_notifications, push_notifications)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        friend_requests = VALUES(friend_requests),
        friend_accepted = VALUES(friend_accepted),
        messages = VALUES(messages),
        system_updates = VALUES(system_updates),
        mood_milestones = VALUES(mood_milestones),
        recommendations = VALUES(recommendations),
        email_notifications = VALUES(email_notifications),
        push_notifications = VALUES(push_notifications)
    ");
    
    $stmt->bind_param("iiiiiiiii", 
        $userId,
        $preferences['friend_requests'] ? 1 : 0,
        $preferences['friend_accepted'] ? 1 : 0,
        $preferences['messages'] ? 1 : 0,
        $preferences['system_updates'] ? 1 : 0,
        $preferences['mood_milestones'] ? 1 : 0,
        $preferences['recommendations'] ? 1 : 0,
        $preferences['email_notifications'] ? 1 : 0,
        $preferences['push_notifications'] ? 1 : 0
    );
    
    return $stmt->execute();
}

/**
 * Clean up expired notifications
 */
function cleanupExpiredNotifications() {
    global $conn;

    // Since we don't have expires_at column, we can clean up old notifications (older than 30 days)
    $stmt = $conn->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    return $stmt->execute();
}

/**
 * Format notification time ago
 */
if (!function_exists('timeAgo')) {
    function timeAgo($datetime) {
        $time = time() - strtotime($datetime);

        if ($time < 60) return 'just now';
        if ($time < 3600) return floor($time/60) . 'm ago';
        if ($time < 86400) return floor($time/3600) . 'h ago';
        if ($time < 2592000) return floor($time/86400) . 'd ago';

        return date('M j, Y', strtotime($datetime));
    }
}
?>
