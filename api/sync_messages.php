<?php
/**
 * Sync Messages API
 * Handles syncing messages when user comes back online
 */

session_start();
require_once '../config.php';
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not authenticated']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Get any pending messages from request
    $input = json_decode(file_get_contents('php://input'), true);
    
    $syncedCount = 0;
    
    if ($input && isset($input['pending_messages']) && is_array($input['pending_messages'])) {
        // Process offline messages
        foreach ($input['pending_messages'] as $message) {
            if (!isset($message['conversation_id']) || !isset($message['content'])) {
                continue;
            }
            
            // Verify user is participant in conversation
            $stmt = $conn->prepare("
                SELECT COUNT(*) as count 
                FROM conversation_participants 
                WHERE conversation_id = ? AND user_id = ?
            ");
            
            if ($stmt) {
                $stmt->bind_param("ii", $message['conversation_id'], $userId);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $stmt->close();
                
                if ($row['count'] > 0) {
                    // Insert message
                    $stmt = $conn->prepare("
                        INSERT INTO messages (sender_id, conversation_id, content, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    
                    if ($stmt) {
                        $stmt->bind_param("iis", $userId, $message['conversation_id'], $message['content']);
                        
                        if ($stmt->execute()) {
                            $syncedCount++;
                            
                            // Update conversation timestamp
                            $updateStmt = $conn->prepare("
                                UPDATE conversations 
                                SET last_message_at = NOW() 
                                WHERE id = ?
                            ");
                            if ($updateStmt) {
                                $updateStmt->bind_param("i", $message['conversation_id']);
                                $updateStmt->execute();
                                $updateStmt->close();
                            }
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
    
    // Get latest messages for user's conversations
    $stmt = $conn->prepare("
        SELECT m.*, u.username, u.display_name 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
        WHERE cp.user_id = ? AND m.created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)
        ORDER BY m.created_at DESC
        LIMIT 50
    ");
    
    $latestMessages = [];
    if ($stmt) {
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $latestMessages[] = $row;
        }
        $stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Messages synced successfully',
        'synced_count' => $syncedCount,
        'latest_messages' => $latestMessages
    ]);
    
} catch (Exception $e) {
    error_log("Sync messages error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to sync messages',
        'error' => $e->getMessage()
    ]);
}
?>
