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

    try {
        $stmt = $conn->prepare("
            SELECT u.*,
                   COALESCE(u.follower_count, 0) as follower_count,
                   COALESCE(u.following_count, 0) as following_count,
                   COALESCE(u.connection_count, 0) as connection_count
            FROM users u
            WHERE u.id = ?
        ");

        $stmt->execute([$userId]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Failed to get user profile with stats: " . $e->getMessage());
        return null;
    }
}

/**
 * Check if current user is following another user
 */
function isFollowing($followerId, $followingId) {
    global $conn;

    $stmt = $conn->prepare("SELECT id FROM user_follows WHERE follower_id = ? AND following_id = ?");

    if (!$stmt) {
        error_log("Failed to prepare isFollowing query: " . $conn->error);
        return false;
    }

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

    // Log the attempt for debugging
    error_log("Attempting to follow user: follower=$followerId, following=$followingId");

    // Check if already following
    if (isFollowing($followerId, $followingId)) {
        error_log("User $followerId is already following user $followingId");
        return false;
    }

    // Check if trying to follow self
    if ($followerId == $followingId) {
        error_log("User $followerId tried to follow themselves");
        return false;
    }

    $conn->begin_transaction();

    try {
        // Check if user_follows table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'user_follows'");
        if ($tableCheck->num_rows === 0) {
            error_log("Error: user_follows table does not exist");
            $conn->rollback();
            return false;
        }

        // Add follow relationship
        $stmt = $conn->prepare("INSERT INTO user_follows (follower_id, following_id) VALUES (?, ?)");
        if (!$stmt) {
            error_log("Failed to prepare follow insert statement: " . $conn->error);
            $conn->rollback();
            return false;
        }

        $stmt->bind_param("ii", $followerId, $followingId);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            error_log("No rows affected when inserting follow relationship");
            $conn->rollback();
            return false;
        }

        // Check if follower_count and following_count columns exist before updating
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'follower_count'");
        if ($columnCheck->num_rows > 0) {
            // Update follower count for the followed user
            $stmt = $conn->prepare("UPDATE users SET follower_count = follower_count + 1 WHERE id = ?");
            $stmt->bind_param("i", $followingId);
            $stmt->execute();

            // Update following count for the follower
            $stmt = $conn->prepare("UPDATE users SET following_count = following_count + 1 WHERE id = ?");
            $stmt->bind_param("i", $followerId);
            $stmt->execute();
        } else {
            error_log("Warning: follower_count/following_count columns do not exist in users table");
        }

        $conn->commit();
        error_log("Successfully followed user: follower=$followerId, following=$followingId");
        return true;
    } catch (Exception $e) {
        error_log("Error following user: " . $e->getMessage());
        $conn->rollback();
        return false;
    }
}

/**
 * Unfollow a user
 */
function unfollowUser($followerId, $followingId) {
    global $conn;

    // Log the attempt for debugging
    error_log("Attempting to unfollow user: follower=$followerId, following=$followingId");

    // Check if user_follows table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'user_follows'");
    if ($tableCheck->num_rows === 0) {
        error_log("Error: user_follows table does not exist");
        return false;
    }

    // Check if actually following
    if (!isFollowing($followerId, $followingId)) {
        error_log("User $followerId is not following user $followingId");
        return false;
    }

    $conn->begin_transaction();

    try {
        // Remove follow relationship
        $stmt = $conn->prepare("DELETE FROM user_follows WHERE follower_id = ? AND following_id = ?");
        if (!$stmt) {
            error_log("Failed to prepare unfollow delete statement: " . $conn->error);
            $conn->rollback();
            return false;
        }

        $stmt->bind_param("ii", $followerId, $followingId);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            error_log("No rows affected when deleting follow relationship");
            $conn->rollback();
            return false;
        }

        // Check if follower_count and following_count columns exist before updating
        $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'follower_count'");
        if ($columnCheck->num_rows > 0) {
            // Update follower count for the unfollowed user
            $stmt = $conn->prepare("UPDATE users SET follower_count = GREATEST(follower_count - 1, 0) WHERE id = ?");
            $stmt->bind_param("i", $followingId);
            $stmt->execute();

            // Update following count for the unfollower
            $stmt = $conn->prepare("UPDATE users SET following_count = GREATEST(following_count - 1, 0) WHERE id = ?");
            $stmt->bind_param("i", $followerId);
            $stmt->execute();
        } else {
            error_log("Warning: follower_count/following_count columns do not exist in users table");
        }

        $conn->commit();
        error_log("Successfully unfollowed user: follower=$followerId, following=$followingId");
        return true;
    } catch (Exception $e) {
        error_log("Error unfollowing user: " . $e->getMessage());
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

    // Log the attempt for debugging
    error_log("Attempting to send connection request: requester=$requesterId, receiver=$receiverId");

    // Include notification functions
    require_once __DIR__ . '/notification_functions.php';

    // Check if user_connections table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'user_connections'");
    if ($tableCheck->num_rows === 0) {
        error_log("Error: user_connections table does not exist");
        return false;
    }

    // Check if connection already exists
    $existingConnection = getConnectionStatus($requesterId, $receiverId);
    if ($existingConnection) {
        error_log("Connection already exists between requester=$requesterId and receiver=$receiverId");
        return false;
    }

    // Check if trying to connect to self
    if ($requesterId == $receiverId) {
        error_log("User $requesterId tried to connect to themselves");
        return false;
    }

    $stmt = $conn->prepare("INSERT INTO user_connections (requester_id, receiver_id, status) VALUES (?, ?, 'pending')");
    if (!$stmt) {
        error_log("Failed to prepare connection request statement: " . $conn->error);
        return false;
    }

    $stmt->bind_param("ii", $requesterId, $receiverId);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            error_log("Successfully sent connection request: requester=$requesterId, receiver=$receiverId");
            // Create notification for the receiver
            try {
                createFriendRequestNotification($receiverId, $requesterId);
            } catch (Exception $e) {
                error_log("Failed to create notification: " . $e->getMessage());
                // Don't fail the connection request if notification fails
            }
            return true;
        } else {
            error_log("No rows affected when inserting connection request");
            return false;
        }
    } else {
        error_log("Failed to execute connection request statement: " . $stmt->error);
        return false;
    }
}

/**
 * Accept connection request
 */
function acceptConnectionRequest($requesterId, $receiverId) {
    global $conn;

    // Include notification functions
    require_once __DIR__ . '/notification_functions.php';

    // Log the attempt for debugging
    error_log("Attempting to accept connection request: requester=$requesterId, receiver=$receiverId");

    $conn->begin_transaction();

    try {
        // First, verify the connection request exists
        $checkStmt = $conn->prepare("
            SELECT id FROM user_connections
            WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
        ");
        $checkStmt->bind_param("ii", $requesterId, $receiverId);
        $checkStmt->execute();
        $result = $checkStmt->get_result();

        if ($result->num_rows === 0) {
            error_log("No pending connection request found for requester=$requesterId, receiver=$receiverId");
            $conn->rollback();
            return false;
        }

        // Update connection status
        $stmt = $conn->prepare("
            UPDATE user_connections
            SET status = 'accepted', updated_at = NOW()
            WHERE requester_id = ? AND receiver_id = ? AND status = 'pending'
        ");
        $stmt->bind_param("ii", $requesterId, $receiverId);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            // Check if connection_count column exists before updating
            $columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'connection_count'");
            if ($columnCheck->num_rows > 0) {
                // Update connection counts
                $stmt = $conn->prepare("UPDATE users SET connection_count = connection_count + 1 WHERE id IN (?, ?)");
                $stmt->bind_param("ii", $requesterId, $receiverId);
                $stmt->execute();
            } else {
                error_log("Warning: connection_count column does not exist in users table");
            }

            // Create notification for the requester (with error handling)
            try {
                createFriendAcceptedNotification($requesterId, $receiverId);
            } catch (Exception $notifError) {
                error_log("Failed to create notification: " . $notifError->getMessage());
                // Don't fail the whole operation for notification errors
            }

            $conn->commit();
            error_log("Connection request accepted successfully");
            return true;
        }

        error_log("No rows affected when updating connection status");
        $conn->rollback();
        return false;
    } catch (Exception $e) {
        error_log("Error accepting connection request: " . $e->getMessage());
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

    // First check what columns exist in the users table
    $columnsQuery = $conn->query("SHOW COLUMNS FROM users");
    $availableColumns = [];
    while ($column = $columnsQuery->fetch_assoc()) {
        $availableColumns[] = $column['Field'];
    }

    // Build SELECT clause based on available columns
    $selectColumns = ['u.id', 'u.username'];

    // Add optional columns if they exist
    if (in_array('display_name', $availableColumns)) {
        $selectColumns[] = 'u.display_name';
    }
    if (in_array('profile_image', $availableColumns)) {
        $selectColumns[] = 'u.profile_image';
    }
    if (in_array('bio', $availableColumns)) {
        $selectColumns[] = 'u.bio';
    }
    if (in_array('is_public', $availableColumns)) {
        $selectColumns[] = 'u.is_public';
    }
    if (in_array('follower_count', $availableColumns)) {
        $selectColumns[] = 'u.follower_count';
    }
    if (in_array('following_count', $availableColumns)) {
        $selectColumns[] = 'u.following_count';
    }

    $selectClause = implode(', ', $selectColumns);

    // Build WHERE clause based on available columns
    $whereConditions = ['u.id != ?'];
    $bindTypes = 'i';
    $bindValues = [$currentUserId];

    // Add search conditions
    if (in_array('display_name', $availableColumns)) {
        $whereConditions[] = '(u.username LIKE ? OR u.display_name LIKE ?)';
        $bindTypes .= 'ss';
        $bindValues[] = $searchTerm;
        $bindValues[] = $searchTerm;
    } else {
        $whereConditions[] = 'u.username LIKE ?';
        $bindTypes .= 's';
        $bindValues[] = $searchTerm;
    }

    // Add public filter if column exists
    if (in_array('is_public', $availableColumns)) {
        $whereConditions[] = 'u.is_public = TRUE';
    }

    // Check if user_blocks table exists
    $tablesQuery = $conn->query("SHOW TABLES LIKE 'user_blocks'");
    if ($tablesQuery && $tablesQuery->num_rows > 0) {
        $whereConditions[] = 'NOT EXISTS (SELECT 1 FROM user_blocks WHERE blocker_id = u.id AND blocked_id = ?)';
        $whereConditions[] = 'NOT EXISTS (SELECT 1 FROM user_blocks WHERE blocker_id = ? AND blocked_id = u.id)';
        $bindTypes .= 'ii';
        $bindValues[] = $currentUserId;
        $bindValues[] = $currentUserId;
    }

    $whereClause = implode(' AND ', $whereConditions);

    $sql = "SELECT $selectClause FROM users u WHERE $whereClause ORDER BY u.username LIMIT ?";
    $bindTypes .= 'i';
    $bindValues[] = $limit;

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare searchUsers query: " . $conn->error);
        return [];
    }

    $stmt->bind_param($bindTypes, ...$bindValues);
    $stmt->execute();
    $result = $stmt->get_result();

    $users = [];
    while ($row = $result->fetch_assoc()) {
        // Set default values for missing columns
        if (!isset($row['display_name'])) {
            $row['display_name'] = $row['username'];
        }
        if (!isset($row['profile_image'])) {
            $row['profile_image'] = null;
        }
        if (!isset($row['bio'])) {
            $row['bio'] = '';
        }
        if (!isset($row['is_public'])) {
            $row['is_public'] = 1;
        }
        if (!isset($row['follower_count'])) {
            $row['follower_count'] = 0;
        }
        if (!isset($row['following_count'])) {
            $row['following_count'] = 0;
        }

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
