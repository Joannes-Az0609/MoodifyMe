<?php
/**
 * MoodifyMe - Check New Messages API
 * Check for new messages across all conversations
 */

require_once '../includes/db_connect.php';

// Set JSON header
header('Content-Type: application/json');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Please log in to check messages.'
    ]);
    exit;
}

$currentUserId = $_SESSION['user_id'];
$lastCheck = $_GET['last_check'] ?? 0;

try {
    // Get new messages since last check
    $stmt = $conn->prepare("
        SELECT 
            m.id,
            m.conversation_id,
            m.content,
            m.created_at,
            m.sender_id,
            u.username as sender_username,
            u.display_name as sender_display_name,
            u.profile_picture as sender_profile_image
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        JOIN conversation_participants cp ON m.conversation_id = cp.conversation_id
        WHERE cp.user_id = ? 
          AND m.sender_id != ? 
          AND UNIX_TIMESTAMP(m.created_at) > ?
        ORDER BY m.created_at DESC
        LIMIT 10
    ");
    
    $stmt->bind_param("iii", $currentUserId, $currentUserId, $lastCheck);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $newMessages = [];
    while ($message = $result->fetch_assoc()) {
        $newMessages[] = [
            'id' => $message['id'],
            'conversation_id' => $message['conversation_id'],
            'content' => $message['content'],
            'created_at' => $message['created_at'],
            'sender' => [
                'id' => $message['sender_id'],
                'username' => $message['sender_username'],
                'display_name' => $message['sender_display_name'],
                'profile_image' => $message['sender_profile_image']
            ],
            'preview' => strlen($message['content']) > 50 ? 
                        substr($message['content'], 0, 50) . '...' : 
                        $message['content']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'new_messages' => $newMessages,
        'count' => count($newMessages),
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    error_log("Check new messages error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error checking for new messages.'
    ]);
}

$conn->close();
?>
