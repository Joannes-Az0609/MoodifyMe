<?php
/**
 * MoodifyMe - Social Functions
 * Functions for social features: following, connections, messaging
 */

/**
 * Get user profile with social stats
 */
function getUserProfileWithStats($userId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT u.*, 
               COALESCE(u.follower_count, 0) as follower_count,
               COALESCE(u.following_count, 0) as following_count,
               COALESCE(u.connection_count, 0) as connection_count
        FROM users u 
        WHERE u.id = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

/**
 * Check if current user is following another user
 */
function isFollowing($followerId, $followingId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ?");
    $stmt->bind_param("ii", $followerId, $followingId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Follow a user
 */
function followUser($followerId, $followingId) {
    global $conn;
    
    // Check if already following
    if (isFollowing($followerId, $followingId)) {
        return false;
    }
    
    // Check if trying to follow self
    if ($followerId == $followingId) {
        return false;
    }
    
    $conn->begin_transaction();
    
    try {
        // Add follow relationship
        $stmt = $conn->prepare("INSERT INTO user_follows (follower_id, following_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $followerId, $followingId);
        $stmt->execute();
        
        // Update follower count for the followed user
        $stmt = $conn->prepare("UPDATE users SET follower_count = follower_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $followingId);
        $stmt->execute();
        
        // Update following count for the follower
        $stmt = $conn->prepare("UPDATE users SET following_count = following_count + 1 WHERE id = ?");
        $stmt->bind_param("i", $followerId);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Unfollow a user
 */
function unfollowUser($followerId, $followingId) {
    global $conn;
    
    // Check if actually following
    if (!isFollowing($followerId, $followingId)) {
        return false;
    }
    
    $conn->begin_transaction();
    
    try {
        // Remove follow relationship
        $stmt = $conn->prepare("DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?");
        $stmt->bind_param("ii", $followerId, $followingId);
        $stmt->execute();
        
        // Update follower count for the unfollowed user
        $stmt = $conn->prepare("UPDATE users SET follower_count = GREATEST(follower_count - 1, 0) WHERE id = ?");
        $stmt->bind_param("i", $followingId);
        $stmt->execute();
        
        // Update following count for the unfollower
        $stmt = $conn->prepare("UPDATE users SET following_count = GREATEST(following_count - 1, 0) WHERE id = ?");
        $stmt->bind_param("i", $followerId);
        $stmt->execute();
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Get connection status between two users
 */
function getConnectionStatus($userId1, $userId2) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT status, requester_id, receiver_id 
        FROM user_connections 
        WHERE (requester_id = ? AND receiver_id = ?) 
           OR (requester_id = ? AND receiver_id = ?)
    ");
    $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Send connection request
 */
function sendConnectionRequest($requesterId, $receiverId) {
    global $conn;
    
    // Check if connection already exists
    if (getConnectionStatus($requesterId, $receiverId)) {
        return false;
    }
    
    // Check if trying to connect to self
    if ($requesterId == $receiverId) {
        return false;
    }
    
    $stmt = $conn->prepare("INSERT INTO user_connections (requester_id, receiver_id, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("ii", $requesterId, $receiverId);
    
    return $stmt->execute();
}

/**
 * Accept connection request
 */
function acceptConnectionRequest($requesterId, $receiverId) {
    global $conn;
    
    $conn->begin_transaction();
    
    try {
        // Update connection status
        $stmt = $conn->prepare("
            UPDATE user_connections 
            SET status = 'accepted', updated_at = NOW() 
            WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("ii", $requesterId, $receiverId);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Update connection counts
            $stmt = $conn->prepare("UPDATE users SET connection_count = connection_count + 1 WHERE id IN (?, ?)");
            $stmt->bind_param("ii", $requesterId, $receiverId);
            $stmt->execute();
            
            $conn->commit();
            return true;
        }
        
        $conn->rollback();
        return false;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Decline connection request
 */
function declineConnectionRequest($requesterId, $receiverId) {
    global $conn;
    
    $stmt = $conn->prepare("
        UPDATE user_connections 
        SET status = 'declined', updated_at = NOW() 
        WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
    ");
    $stmt->bind_param("ii", $requesterId, $receiverId);
    
    return $stmt->execute() && $stmt->affected_rows > 0;
}

/**
 * Remove connection
 */
function removeConnection($userId1, $userId2) {
    global $conn;
    
    $conn->begin_transaction();
    
    try {
        // Remove connection
        $stmt = $conn->prepare("
            DELETE FROM user_connections 
            WHERE (requester_id = ? AND receiver_id = ?) 
               OR (requester_id = ? AND receiver_id = ?)
        ");
        $stmt->bind_param("iiii", $userId1, $userId2, $userId2, $userId1);
        $stmt->execute();
        
        if ($stmt->affected_rows > 0) {
            // Update connection counts
            $stmt = $conn->prepare("UPDATE users SET connection_count = GREATEST(connection_count - 1, 0) WHERE id IN (?, ?)");
            $stmt->bind_param("ii", $userId1, $userId2);
            $stmt->execute();
            
            $conn->commit();
            return true;
        }
        
        $conn->rollback();
        return false;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Check if user is blocked
 */
function isUserBlocked($blockerId, $blockedId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id FROM user_blocks WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->bind_param("ii", $blockerId, $blockedId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Block a user
 */
function blockUser($blockerId, $blockedId, $reason = null) {
    global $conn;
    
    if ($blockerId == $blockedId) {
        return false;
    }
    
    $conn->begin_transaction();
    
    try {
        // Add block
        $stmt = $conn->prepare("INSERT INTO user_blocks (blocker_id, blocked_id, reason) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE reason = VALUES(reason)");
        $stmt->bind_param("iis", $blockerId, $blockedId, $reason);
        $stmt->execute();
        
        // Remove any existing connections
        removeConnection($blockerId, $blockedId);
        
        // Remove follow relationships
        unfollowUser($blockerId, $blockedId);
        unfollowUser($blockedId, $blockerId);
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

/**
 * Unblock a user
 */
function unblockUser($blockerId, $blockedId) {
    global $conn;
    
    $stmt = $conn->prepare("DELETE FROM user_blocks WHERE blocker_id = ? AND blocked_id = ?");
    $stmt->bind_param("ii", $blockerId, $blockedId);
    
    return $stmt->execute();
}

/**
 * Search users for connections
 */
function searchUsers($query, $currentUserId, $limit = 20) {
    global $conn;
    
    $searchTerm = "%$query%";
    
    $stmt = $conn->prepare("
        SELECT u.id, u.username, u.display_name, u.profile_image, u.bio, u.is_public,
               u.follower_count, u.following_count
        FROM users u
        WHERE u.id != ? 
          AND (u.username LIKE ? OR u.display_name LIKE ?)
          AND u.is_public = TRUE
          AND NOT EXISTS (SELECT 1 FROM user_blocks WHERE blocker_id = u.id AND blocked_id = ?)
          AND NOT EXISTS (SELECT 1 FROM user_blocks WHERE blocker_id = ? AND blocked_id = u.id)
        ORDER BY u.username
        LIMIT ?
    ");
    $stmt->bind_param("isssii", $currentUserId, $searchTerm, $searchTerm, $currentUserId, $currentUserId, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}

/**
 * Update user online status
 */
function updateUserOnlineStatus($userId, $status = 'online') {
    global $conn;
    
    $stmt = $conn->prepare("
        INSERT INTO user_online_status (user_id, status, last_seen) 
        VALUES (?, ?, NOW()) 
        ON DUPLICATE KEY UPDATE status = VALUES(status), last_seen = NOW()
    ");
    $stmt->bind_param("is", $userId, $status);
    
    return $stmt->execute();
}

/**
 * Get user online status
 */
function getUserOnlineStatus($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT status, last_seen FROM user_online_status WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return ['status' => 'offline', 'last_seen' => null];
}
?>
